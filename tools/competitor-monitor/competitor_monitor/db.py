"""SQLite schema en helpers voor de competitor monitor."""

from __future__ import annotations

import sqlite3
from contextlib import contextmanager
from dataclasses import dataclass
from datetime import date, datetime
from pathlib import Path
from typing import Iterator, Optional

SCHEMA = """
CREATE TABLE IF NOT EXISTS competitors (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    url TEXT NOT NULL UNIQUE,
    capacity INTEGER,
    listing_type TEXT,
    rating REAL,
    distance_km REAL,
    first_seen TEXT NOT NULL,
    last_seen TEXT NOT NULL,
    missed_runs INTEGER NOT NULL DEFAULT 0,
    active INTEGER NOT NULL DEFAULT 1,
    ignored_manually INTEGER NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS price_snapshots (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    competitor_id INTEGER NOT NULL REFERENCES competitors(id),
    checkin_date TEXT NOT NULL,
    price_per_night REAL,
    available INTEGER NOT NULL,
    scraped_at TEXT NOT NULL,
    UNIQUE(competitor_id, checkin_date, scraped_at)
);

CREATE INDEX IF NOT EXISTS idx_snapshots_competitor_date
    ON price_snapshots(competitor_id, checkin_date);

CREATE TABLE IF NOT EXISTS run_log (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    run_type TEXT NOT NULL,
    started_at TEXT NOT NULL,
    finished_at TEXT,
    status TEXT NOT NULL,
    detail TEXT
);
"""


@dataclass
class Competitor:
    id: Optional[int]
    name: str
    url: str
    capacity: Optional[int]
    listing_type: Optional[str]
    rating: Optional[float]
    distance_km: Optional[float]
    active: bool = True
    ignored_manually: bool = False


def connect(db_path: str | Path) -> sqlite3.Connection:
    path = Path(db_path)
    path.parent.mkdir(parents=True, exist_ok=True)
    conn = sqlite3.connect(str(path))
    conn.row_factory = sqlite3.Row
    conn.execute("PRAGMA foreign_keys = ON")
    conn.executescript(SCHEMA)
    return conn


@contextmanager
def open_db(db_path: str | Path) -> Iterator[sqlite3.Connection]:
    conn = connect(db_path)
    try:
        yield conn
        conn.commit()
    finally:
        conn.close()


def upsert_competitor(conn: sqlite3.Connection, comp: Competitor) -> int:
    """Insert a new competitor, or refresh an existing one found again this run."""
    now = datetime.utcnow().isoformat()
    row = conn.execute(
        "SELECT id, ignored_manually FROM competitors WHERE url = ?", (comp.url,)
    ).fetchone()
    if row is None:
        cur = conn.execute(
            """
            INSERT INTO competitors
                (name, url, capacity, listing_type, rating, distance_km,
                 first_seen, last_seen, missed_runs, active, ignored_manually)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, 1, 0)
            """,
            (
                comp.name,
                comp.url,
                comp.capacity,
                comp.listing_type,
                comp.rating,
                comp.distance_km,
                now,
                now,
            ),
        )
        return cur.lastrowid

    competitor_id = row["id"]
    ignored = bool(row["ignored_manually"])
    conn.execute(
        """
        UPDATE competitors
        SET name = ?, capacity = ?, listing_type = ?, rating = ?, distance_km = ?,
            last_seen = ?, missed_runs = 0,
            active = CASE WHEN ? THEN active ELSE 1 END
        WHERE id = ?
        """,
        (
            comp.name,
            comp.capacity,
            comp.listing_type,
            comp.rating,
            comp.distance_km,
            now,
            ignored,
            competitor_id,
        ),
    )
    return competitor_id


def mark_missing(conn: sqlite3.Connection, seen_urls: set[str], missed_runs_before_deactivate: int) -> None:
    """Bump missed_runs for competitors not found in the current discovery run,
    and deactivate ones past the configured threshold. Rows are never deleted."""
    rows = conn.execute(
        "SELECT id, url, missed_runs FROM competitors WHERE active = 1"
    ).fetchall()
    for row in rows:
        if row["url"] in seen_urls:
            continue
        missed = row["missed_runs"] + 1
        active = 0 if missed >= missed_runs_before_deactivate else 1
        conn.execute(
            "UPDATE competitors SET missed_runs = ?, active = ? WHERE id = ?",
            (missed, active, row["id"]),
        )


def set_ignored(conn: sqlite3.Connection, competitor_id: int, ignored: bool) -> None:
    conn.execute(
        "UPDATE competitors SET ignored_manually = ?, active = CASE WHEN ? THEN 0 ELSE active END WHERE id = ?",
        (int(ignored), int(ignored), competitor_id),
    )


def active_competitors(conn: sqlite3.Connection) -> list[sqlite3.Row]:
    return conn.execute(
        "SELECT * FROM competitors WHERE active = 1 AND ignored_manually = 0"
    ).fetchall()


def record_snapshot(
    conn: sqlite3.Connection,
    competitor_id: int,
    checkin_date: date,
    price_per_night: Optional[float],
    available: bool,
) -> None:
    now = datetime.utcnow().isoformat()
    conn.execute(
        """
        INSERT OR REPLACE INTO price_snapshots
            (competitor_id, checkin_date, price_per_night, available, scraped_at)
        VALUES (?, ?, ?, ?, ?)
        """,
        (competitor_id, checkin_date.isoformat(), price_per_night, int(available), now),
    )


def latest_snapshots_for_date(conn: sqlite3.Connection, checkin_date: date) -> list[sqlite3.Row]:
    """Most recent snapshot per active competitor for a given check-in date."""
    return conn.execute(
        """
        SELECT ps.*
        FROM price_snapshots ps
        JOIN competitors c ON c.id = ps.competitor_id
        WHERE ps.checkin_date = ?
          AND c.active = 1 AND c.ignored_manually = 0
          AND ps.scraped_at = (
              SELECT MAX(scraped_at) FROM price_snapshots ps2
              WHERE ps2.competitor_id = ps.competitor_id AND ps2.checkin_date = ps.checkin_date
          )
        """,
        (checkin_date.isoformat(),),
    ).fetchall()


def log_run(conn: sqlite3.Connection, run_type: str, status: str, detail: str = "") -> int:
    now = datetime.utcnow().isoformat()
    cur = conn.execute(
        "INSERT INTO run_log (run_type, started_at, status, detail) VALUES (?, ?, ?, ?)",
        (run_type, now, status, detail),
    )
    return cur.lastrowid


def finish_run(conn: sqlite3.Connection, run_id: int, status: str, detail: str = "") -> None:
    now = datetime.utcnow().isoformat()
    conn.execute(
        "UPDATE run_log SET finished_at = ?, status = ?, detail = ? WHERE id = ?",
        (now, status, detail, run_id),
    )
