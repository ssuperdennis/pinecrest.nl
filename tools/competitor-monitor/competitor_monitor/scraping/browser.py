"""Playwright browser setup: headless, realistische user-agent, throttling."""

from __future__ import annotations

import asyncio
import logging
import os
import random
from contextlib import asynccontextmanager
from typing import AsyncIterator

from playwright.async_api import Browser, BrowserContext, Page, async_playwright

logger = logging.getLogger("competitor_monitor.browser")

# Als de omgeving een losstaand chromium-binary meelevert (bijv. een
# preinstalled-browser sandbox met een pad dat niet overeenkomt met de
# playwright-pip-versie), gebruik dat direct in plaats van playwright zijn
# eigen (mogelijk ontbrekende) download te laten zoeken.
_PREINSTALLED_CHROMIUM = "/opt/pw-browsers/chromium"


class Throttle:
    """Willekeurige delay tussen requests om Booking.com niet te bestoken."""

    def __init__(self, min_seconds: float, max_seconds: float):
        self.min_seconds = min_seconds
        self.max_seconds = max_seconds

    async def wait(self) -> None:
        delay = random.uniform(self.min_seconds, self.max_seconds)
        await asyncio.sleep(delay)


@asynccontextmanager
async def launch_context(
    headless: bool = True,
    user_agent: str = (
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 "
        "(KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36"
    ),
    navigation_timeout_ms: int = 30000,
) -> AsyncIterator[BrowserContext]:
    async with async_playwright() as p:
        launch_kwargs = {"headless": headless}
        if os.path.exists(_PREINSTALLED_CHROMIUM):
            launch_kwargs["executable_path"] = _PREINSTALLED_CHROMIUM
        browser: Browser = await p.chromium.launch(**launch_kwargs)
        context: BrowserContext = await browser.new_context(
            user_agent=user_agent,
            locale="nl-NL",
            viewport={"width": 1366, "height": 900},
        )
        context.set_default_navigation_timeout(navigation_timeout_ms)
        context.set_default_timeout(navigation_timeout_ms)
        try:
            yield context
        finally:
            await context.close()
            await browser.close()


async def new_page(context: BrowserContext) -> Page:
    page = await context.new_page()
    return page


async def debug_screenshot(page: Page, screenshot_dir: str, label: str) -> str:
    """Schrijft een screenshot + HTML-dump weg voor debugging bij een mislukte parse."""
    import os
    from datetime import datetime

    os.makedirs(screenshot_dir, exist_ok=True)
    timestamp = datetime.utcnow().strftime("%Y%m%dT%H%M%S")
    safe_label = "".join(c if c.isalnum() or c in "-_" else "_" for c in label)[:80]
    base = f"{timestamp}_{safe_label}"

    screenshot_path = os.path.join(screenshot_dir, f"{base}.png")
    html_path = os.path.join(screenshot_dir, f"{base}.html")

    try:
        await page.screenshot(path=screenshot_path, full_page=True)
    except Exception:
        logger.exception("Kon geen screenshot maken voor %s", label)

    try:
        content = await page.content()
        with open(html_path, "w", encoding="utf-8") as f:
            f.write(content)
    except Exception:
        logger.exception("Kon geen HTML-dump maken voor %s", label)

    logger.error("Debug-output weggeschreven: %s / %s", screenshot_path, html_path)
    return screenshot_path
