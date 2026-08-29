"""Stap 1: concurrentieonderzoek op Booking.com.

Doorloopt de geconfigureerde zoektermen, verzamelt kandidaat-listings,
filtert op relevantie (relevance.py) en schrijft de resultaten weg naar
de competitors-tabel. Bedoeld om wekelijks te draaien.
"""

from __future__ import annotations

import logging
from urllib.parse import quote_plus

from playwright.async_api import Page

from . import db
from .extract import parse_capacity, parse_price, parse_rating
from .relevance import ListingCandidate, RelevanceConfig, distance_km, is_relevant
from .scraping import selectors
from .scraping.browser import Throttle, debug_screenshot, launch_context, new_page

logger = logging.getLogger("competitor_monitor.discovery")

SEARCH_URL_TEMPLATE = "https://www.booking.com/searchresults.nl.html?ss={query}"


async def _first_matching(page_or_locator, selector_list: list[str]):
    for sel in selector_list:
        loc = page_or_locator.locator(sel)
        if await loc.count() > 0:
            return loc.first
    return None


async def _first_text(page_or_locator, selector_list: list[str]) -> str | None:
    loc = await _first_matching(page_or_locator, selector_list)
    if loc is None:
        return None
    try:
        text = await loc.inner_text()
        return text.strip()
    except Exception:
        return None


async def _extract_candidate(card, screenshot_dir: str, debug: bool) -> ListingCandidate | None:
    name = await _first_text(card, selectors.RESULT_TITLE)
    anchor = await _first_matching(card, selectors.RESULT_URL_ANCHOR)
    url = None
    if anchor is not None:
        try:
            url = await anchor.get_attribute("href")
        except Exception:
            url = None

    if not name or not url:
        logger.error("Kon naam of URL niet uit listing-kaart lezen (name=%r, url=%r)", name, url)
        if debug:
            await debug_screenshot(card.page, screenshot_dir, "card-missing-name-or-url")
        return None

    capacity_text = await _first_text(card, selectors.RESULT_CAPACITY)
    rating_text = await _first_text(card, selectors.RESULT_RATING)
    price_text = await _first_text(card, selectors.RESULT_PRICE)

    return ListingCandidate(
        name=name,
        url=url.split("?")[0],
        capacity=parse_capacity(capacity_text),
        rating=parse_rating(rating_text),
        price=parse_price(price_text),
        latitude=None,
        longitude=None,
    )


async def _fetch_coordinates(page: Page, url: str, timeout_ms: int) -> tuple[float | None, float | None]:
    try:
        await page.goto(url, timeout=timeout_ms)
    except Exception:
        logger.exception("Kon listingpagina niet laden voor coördinaten: %s", url)
        return None, None

    lat = lon = None
    try:
        lat_meta = page.locator('meta[property="place:location:latitude"]')
        lon_meta = page.locator('meta[property="place:location:longitude"]')
        if await lat_meta.count() > 0 and await lon_meta.count() > 0:
            lat = float(await lat_meta.first.get_attribute("content"))
            lon = float(await lon_meta.first.get_attribute("content"))
    except Exception:
        logger.warning("Kon coördinaten niet parsen voor %s, afstand blijft onbekend", url)
    return lat, lon


async def run_discovery(config: dict, db_path: str, debug: bool = False, screenshot_dir: str = "screenshots") -> dict:
    """Voert het volledige discovery-proces uit. Retourneert samenvattende stats."""
    scraping_cfg = config["scraping"]
    rel_cfg = RelevanceConfig(
        capacity_min=config["capacity_min"],
        capacity_max=config["capacity_max"],
        type_keywords=config["type_keywords"],
        origin_lat=config["origin"]["latitude"],
        origin_lon=config["origin"]["longitude"],
        max_distance_km=config["max_distance_km"],
    )
    throttle = Throttle(scraping_cfg["min_delay_seconds"], scraping_cfg["max_delay_seconds"])
    timeout_ms = scraping_cfg["navigation_timeout_ms"]
    max_pages = scraping_cfg["max_search_pages"]

    seen_urls: set[str] = set()
    stats = {"terms_searched": 0, "candidates_seen": 0, "relevant_found": 0, "errors": 0}

    async with launch_context(
        headless=scraping_cfg["headless"],
        user_agent=scraping_cfg["user_agent"],
        navigation_timeout_ms=timeout_ms,
    ) as context:
        list_page = await new_page(context)
        detail_page = await new_page(context)

        for term in config["search_terms"]:
            stats["terms_searched"] += 1
            search_url = SEARCH_URL_TEMPLATE.format(query=quote_plus(term))
            logger.info("Zoekterm: %r -> %s", term, search_url)

            try:
                await list_page.goto(search_url, timeout=timeout_ms)
            except Exception:
                logger.exception("Kon zoekresultatenpagina niet laden voor term %r", term)
                stats["errors"] += 1
                continue

            for page_num in range(1, max_pages + 1):
                cards_loc = None
                for sel in selectors.SEARCH_RESULT_CARD:
                    loc = list_page.locator(sel)
                    if await loc.count() > 0:
                        cards_loc = loc
                        break

                if cards_loc is None:
                    logger.error(
                        "Geen listing-kaarten gevonden voor term %r (pagina %d) - "
                        "selectors mogelijk verouderd, zie selectors.py",
                        term, page_num,
                    )
                    if debug:
                        await debug_screenshot(list_page, screenshot_dir, f"no-cards-{term}-p{page_num}")
                    stats["errors"] += 1
                    break

                count = await cards_loc.count()
                logger.info("Term %r, pagina %d: %d kaarten gevonden", term, page_num, count)

                for i in range(count):
                    card = cards_loc.nth(i)
                    stats["candidates_seen"] += 1
                    try:
                        candidate = await _extract_candidate(card, screenshot_dir, debug)
                    except Exception:
                        logger.exception("Onverwachte fout bij verwerken van listing-kaart #%d voor term %r", i, term)
                        stats["errors"] += 1
                        continue

                    if candidate is None:
                        stats["errors"] += 1
                        continue

                    # Snelle prefilter (capaciteit + type) op basis van de
                    # zoekresultaten-kaart, voordat we een extra pagina-load
                    # doen voor coördinaten (scheelt requests).
                    from .relevance import capacity_ok, type_ok

                    if not (capacity_ok(candidate, rel_cfg) and type_ok(candidate, rel_cfg)):
                        continue

                    await throttle.wait()
                    lat, lon = await _fetch_coordinates(detail_page, candidate.url, timeout_ms)
                    candidate.latitude, candidate.longitude = lat, lon

                    if not is_relevant(candidate, rel_cfg):
                        continue

                    stats["relevant_found"] += 1
                    seen_urls.add(candidate.url)

                    with db.open_db(db_path) as conn:
                        db.upsert_competitor(
                            conn,
                            db.Competitor(
                                id=None,
                                name=candidate.name,
                                url=candidate.url,
                                capacity=candidate.capacity,
                                listing_type=None,
                                rating=candidate.rating,
                                distance_km=distance_km(candidate, rel_cfg),
                            ),
                        )

                await throttle.wait()

                next_button = None
                for sel in selectors.NEXT_PAGE_BUTTON:
                    loc = list_page.locator(sel)
                    if await loc.count() > 0 and await loc.first.is_enabled():
                        next_button = loc.first
                        break

                if next_button is None:
                    break

                try:
                    await next_button.click()
                    await list_page.wait_for_load_state("networkidle", timeout=timeout_ms)
                except Exception:
                    logger.exception("Kon niet doorklikken naar volgende pagina voor term %r", term)
                    stats["errors"] += 1
                    break

    with db.open_db(db_path) as conn:
        db.mark_missing(conn, seen_urls, config["missed_runs_before_deactivate"])

    logger.info("Discovery klaar: %s", stats)
    return stats
