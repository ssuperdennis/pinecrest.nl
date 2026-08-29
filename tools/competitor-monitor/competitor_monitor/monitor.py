"""Stap 2: dagelijkse prijs/beschikbaarheid-monitoring voor actieve concurrenten.

Voor elke actieve concurrent-URL wordt voor de komende N dagen (config:
monitor_horizon_days) de prijs voor een 1-nacht verblijf en de
beschikbaarheid opgehaald, door de listingpagina te openen met
checkin/checkout query-parameters. Dit is trager dan één keer de
kalender uitlezen, maar aanzienlijk robuuster tegen DOM-wijzigingen in
Booking's kalenderwidget.
"""

from __future__ import annotations

import logging
from datetime import date, timedelta
from urllib.parse import urlencode, urlparse, urlunparse, parse_qsl

from playwright.async_api import Page

from . import db
from .extract import parse_price
from .scraping import selectors
from .scraping.browser import Throttle, debug_screenshot, launch_context, new_page

logger = logging.getLogger("competitor_monitor.monitor")


def _url_with_dates(url: str, checkin: date, checkout: date) -> str:
    parsed = urlparse(url)
    query = dict(parse_qsl(parsed.query))
    query["checkin"] = checkin.isoformat()
    query["checkout"] = checkout.isoformat()
    new_query = urlencode(query)
    return urlunparse(parsed._replace(query=new_query))


async def _scrape_one_date(page: Page, base_url: str, checkin: date, timeout_ms: int) -> tuple[float | None, bool]:
    """Retourneert (prijs_per_nacht, beschikbaar)."""
    checkout = checkin + timedelta(days=1)
    url = _url_with_dates(base_url, checkin, checkout)

    try:
        await page.goto(url, timeout=timeout_ms)
    except Exception:
        logger.exception("Kon listingpagina niet laden voor %s op %s", base_url, checkin)
        raise

    sold_out_loc = None
    for sel in selectors.SOLD_OUT_BANNER:
        loc = page.locator(sel)
        if await loc.count() > 0:
            sold_out_loc = loc
            break

    if sold_out_loc is not None:
        return None, False

    price = None
    for sel in selectors.AVAILABILITY_DAY_PRICE + selectors.RESULT_PRICE:
        loc = page.locator(sel)
        if await loc.count() > 0:
            try:
                text = await loc.first.inner_text()
                price = parse_price(text)
                if price is not None:
                    break
            except Exception:
                continue

    return price, True


async def run_monitor(config: dict, db_path: str, debug: bool = False, screenshot_dir: str = "screenshots") -> dict:
    scraping_cfg = config["scraping"]
    throttle = Throttle(scraping_cfg["min_delay_seconds"], scraping_cfg["max_delay_seconds"])
    timeout_ms = scraping_cfg["navigation_timeout_ms"]
    horizon_days = config["monitor_horizon_days"]

    with db.open_db(db_path) as conn:
        competitors = db.active_competitors(conn)

    stats = {"competitors": len(competitors), "snapshots_written": 0, "errors": 0}
    logger.info("Start monitoring van %d actieve concurrenten voor %d dagen", len(competitors), horizon_days)

    today = date.today()

    async with launch_context(
        headless=scraping_cfg["headless"],
        user_agent=scraping_cfg["user_agent"],
        navigation_timeout_ms=timeout_ms,
    ) as context:
        page = await new_page(context)

        for comp in competitors:
            logger.info("Concurrent: %s (%s)", comp["name"], comp["url"])
            for offset in range(horizon_days):
                checkin = today + timedelta(days=offset)
                try:
                    price, available = await _scrape_one_date(page, comp["url"], checkin, timeout_ms)
                except Exception:
                    stats["errors"] += 1
                    if debug:
                        await debug_screenshot(
                            page, screenshot_dir, f"monitor-fail-{comp['id']}-{checkin.isoformat()}"
                        )
                    await throttle.wait()
                    continue

                with db.open_db(db_path) as conn:
                    db.record_snapshot(conn, comp["id"], checkin, price, available)
                stats["snapshots_written"] += 1

                await throttle.wait()

    logger.info("Monitoring klaar: %s", stats)
    return stats
