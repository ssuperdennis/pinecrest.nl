#!/usr/bin/env python3
"""Concurrenten bekijken en handmatig false positives negeren.

Gebruik:
    python scripts/manage_competitors.py list
    python scripts/manage_competitors.py ignore <id>
    python scripts/manage_competitors.py unignore <id>
"""

from __future__ import annotations

import argparse
import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parent.parent))

from competitor_monitor import config, db


def cmd_list(_args: argparse.Namespace) -> int:
    with db.open_db(config.db_path()) as conn:
        rows = conn.execute(
            "SELECT id, name, url, capacity, distance_km, active, ignored_manually, missed_runs "
            "FROM competitors ORDER BY active DESC, ignored_manually ASC, name ASC"
        ).fetchall()

    if not rows:
        print("Geen concurrenten in de database. Draai eerst run_discovery.py.")
        return 0

    for r in rows:
        status = "genegeerd" if r["ignored_manually"] else ("actief" if r["active"] else "inactief")
        dist = f"{r['distance_km']:.1f}km" if r["distance_km"] is not None else "afstand onbekend"
        print(f"[{r['id']:>4}] ({status:9}) {r['name']} — {dist}, {r['capacity']}p — {r['url']}")
    return 0


def cmd_set_ignored(args: argparse.Namespace, ignored: bool) -> int:
    with db.open_db(config.db_path()) as conn:
        row = conn.execute("SELECT id, name FROM competitors WHERE id = ?", (args.id,)).fetchone()
        if row is None:
            print(f"Geen concurrent met id {args.id}")
            return 1
        db.set_ignored(conn, args.id, ignored)
    action = "genegeerd" if ignored else "weer actief gezet"
    print(f"{row['name']} (id {args.id}) is {action}.")
    return 0


def main() -> int:
    parser = argparse.ArgumentParser(description="Beheer van gedetecteerde concurrenten")
    sub = parser.add_subparsers(dest="command", required=True)

    sub.add_parser("list", help="Toon alle concurrenten met status")

    ignore_p = sub.add_parser("ignore", help="Markeer een concurrent als false positive")
    ignore_p.add_argument("id", type=int)

    unignore_p = sub.add_parser("unignore", help="Haal de genegeerd-markering weg")
    unignore_p.add_argument("id", type=int)

    args = parser.parse_args()

    if args.command == "list":
        return cmd_list(args)
    if args.command == "ignore":
        return cmd_set_ignored(args, True)
    if args.command == "unignore":
        return cmd_set_ignored(args, False)
    return 1


if __name__ == "__main__":
    sys.exit(main())
