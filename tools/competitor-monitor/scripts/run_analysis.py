#!/usr/bin/env python3
"""Entrypoint voor stap 4/5: eigen prijzen vergelijken met concurrentie en
waarschuwen bij afwijking >15%. Draai dit na run_monitor.py, bijvoorbeeld
dagelijks in dezelfde cronjob.

Gebruik:
    python scripts/run_analysis.py [--csv PAD_NAAR_HOSTAWAY_EXPORT.csv]
"""

from __future__ import annotations

import argparse
import logging
import sys
import traceback
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parent.parent))

from competitor_monitor import config, db
from competitor_monitor.analysis import compare_own_prices, flagged_only
from competitor_monitor.notify import notify_run_failure, notify_deviations
from competitor_monitor.own_prices import load_own_prices

logger = logging.getLogger("competitor_monitor.run_analysis")


def main(csv_path: str) -> int:
    config.setup_logging("analysis", debug=False)
    cfg = config.load_search_config()
    db_path = config.db_path()

    with db.open_db(db_path) as conn:
        run_id = db.log_run(conn, "analysis", "running")

    try:
        own_prices = load_own_prices(csv_path)
        with db.open_db(db_path) as conn:
            comparisons = compare_own_prices(conn, own_prices, cfg["deviation_threshold"])

        flagged = flagged_only(comparisons)
        logger.info("%d datapunten vergeleken, %d afwijkingen >%.0f%%",
                     len(comparisons), len(flagged), cfg["deviation_threshold"] * 100)

        for c in flagged:
            logger.warning(
                "AFWIJKING %s (%s): eigen €%.2f vs. gemiddeld €%s (%.1f%%, n=%d)",
                c.checkin_date, c.own_listing, c.own_price,
                f"{c.competitor_mean:.2f}" if c.competitor_mean else "n/b",
                (c.deviation_fraction or 0) * 100, c.competitor_count,
            )

        if flagged:
            notify_deviations(flagged)

    except Exception:
        detail = traceback.format_exc()
        logger.error("Analysis-run mislukt:\n%s", detail)
        with db.open_db(db_path) as conn:
            db.finish_run(conn, run_id, "failed", detail[-2000:])
        notify_run_failure("analysis", detail[-500:])
        return 1

    with db.open_db(db_path) as conn:
        db.finish_run(conn, run_id, "success", f"{len(comparisons)} vergeleken, {len(flagged)} afwijkingen")

    return 0


if __name__ == "__main__":
    parser = argparse.ArgumentParser(description="Eigen prijzen vergelijken met concurrentie")
    parser.add_argument("--csv", default=None, help="Pad naar Hostaway CSV-export (default: .env OWN_PRICES_CSV_PATH)")
    args = parser.parse_args()
    csv_path = args.csv or config.own_prices_csv_path()
    sys.exit(main(csv_path))
