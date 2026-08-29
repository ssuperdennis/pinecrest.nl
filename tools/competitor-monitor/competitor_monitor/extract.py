"""Hulpfuncties om ruwe tekst/attributen om te zetten in genormaliseerde
waarden (capaciteit, rating, prijs). Gescheiden van de Playwright-code
zodat dit zonder browser te testen is."""

from __future__ import annotations

import re
from typing import Optional

_CAPACITY_RE = re.compile(r"(\d+)\s*(?:persoon|personen|gast|gasten|adults?|guests?)", re.IGNORECASE)
_PRICE_RE = re.compile(r"[\d.,]+")
_RATING_RE = re.compile(r"\d+[.,]\d+")


def parse_capacity(text: Optional[str]) -> Optional[int]:
    if not text:
        return None
    match = _CAPACITY_RE.search(text)
    if not match:
        return None
    return int(match.group(1))


def parse_price(text: Optional[str]) -> Optional[float]:
    if not text:
        return None
    match = _PRICE_RE.search(text.replace("\xa0", " "))
    if not match:
        return None
    raw = match.group(0)
    # Europese notatie: duizendtal-punt, komma als decimaal.
    if "," in raw and "." in raw:
        raw = raw.replace(".", "").replace(",", ".")
    elif "," in raw:
        raw = raw.replace(",", ".")
    try:
        return float(raw)
    except ValueError:
        return None


def parse_rating(text: Optional[str]) -> Optional[float]:
    if not text:
        return None
    match = _RATING_RE.search(text)
    if not match:
        return None
    try:
        return float(match.group(0).replace(",", "."))
    except ValueError:
        return None
