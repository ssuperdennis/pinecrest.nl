#!/usr/bin/env python3
"""Entrypoint voor stap 1: concurrentieonderzoek. Wekelijks via cron te draaien.

Gebruik:
    python scripts/run_discovery.py [--debug]
"""

from __future__ import annotations

import argparse
import asyncio
import logging
import sys
import traceback
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parent.parent))

from competitor_monitor import config, db
from competitor_monitor.discovery import run_discovery
from competitor_monitor.notify import notify_run_failure

logger = logging.getLogger("competitor_monitor.run_discovery")


async def main(debug: bool) -> int:
    config.setup_logging("discovery", debug=debug)
    cfg = config.load_search_config()
    db_path = config.db_path()

    with db.open_db(db_path) as conn:
        run_id = db.log_run(conn, "discovery", "running")

    try:
        stats = await run_discovery(cfg, db_path, debug=debug, screenshot_dir=config.screenshot_dir())
    except Exception:
        detail = traceback.format_exc()
        logger.error("Discovery-run mislukt:\n%s", detail)
        with db.open_db(db_path) as conn:
            db.finish_run(conn, run_id, "failed", detail[-2000:])
        notify_run_failure("discovery", detail[-500:])
        return 1

    with db.open_db(db_path) as conn:
        db.finish_run(conn, run_id, "success", str(stats))

    logger.info("Discovery-run succesvol afgerond: %s", stats)
    return 0


if __name__ == "__main__":
    parser = argparse.ArgumentParser(description="Concurrentieonderzoek op Booking.com")
    parser.add_argument("--debug", action="store_true", help="Screenshot + HTML-dump bij mislukte parses")
    args = parser.parse_args()
    sys.exit(asyncio.run(main(args.debug)))
