"""Inlezen van de eigen prijzen (CSV-export uit Hostaway).

Hostaway's exportkolommen kunnen per rapport verschillen; deze parser
zoekt naar de meest voorkomende kolomnamen (hoofdletterongevoelig) en
geeft een duidelijke foutmelding als een verplichte kolom ontbreekt,
in plaats van stil verkeerde data te produceren.

Verwacht CSV met tenminste:
  - een datumkolom (bijv. "date", "arrivalDate", "Datum")
  - een prijskolom (bijv. "price", "nightlyRate", "Prijs")
Optioneel:
  - een listingnaam-kolom (bijv. "listingName", "Accommodatie") zodat
    beide huisjes apart vergeleken kunnen worden. Ontbreekt deze kolom,
    dan wordt alles onder listing "default" gezet.
"""

from __future__ import annotations

import csv
from dataclasses import dataclass
from datetime import date, datetime
from pathlib import Path

DATE_COLUMN_ALIASES = ["date", "arrivaldate", "datum", "check-in", "checkin"]
PRICE_COLUMN_ALIASES = ["price", "nightlyrate", "prijs", "rate", "amount"]
LISTING_COLUMN_ALIASES = ["listingname", "listing", "accommodatie", "property", "unit"]

DATE_FORMATS = ["%Y-%m-%d", "%d-%m-%Y", "%d/%m/%Y", "%m/%d/%Y"]


@dataclass
class OwnPricePoint:
    listing: str
    checkin_date: date
    price: float


def _find_column(fieldnames: list[str], aliases: list[str]) -> str | None:
    lower_map = {name.lower().strip(): name for name in fieldnames}
    for alias in aliases:
        if alias in lower_map:
            return lower_map[alias]
    return None


def _parse_date(raw: str) -> date:
    raw = raw.strip()
    for fmt in DATE_FORMATS:
        try:
            return datetime.strptime(raw, fmt).date()
        except ValueError:
            continue
    raise ValueError(f"Onbekend datumformaat: {raw!r}")


def load_own_prices(csv_path: str | Path) -> list[OwnPricePoint]:
    path = Path(csv_path)
    if not path.exists():
        raise FileNotFoundError(f"Own-prices CSV niet gevonden: {path}")

    with open(path, newline="", encoding="utf-8-sig") as f:
        reader = csv.DictReader(f)
        if reader.fieldnames is None:
            raise ValueError(f"CSV lijkt leeg: {path}")

        date_col = _find_column(reader.fieldnames, DATE_COLUMN_ALIASES)
        price_col = _find_column(reader.fieldnames, PRICE_COLUMN_ALIASES)
        listing_col = _find_column(reader.fieldnames, LISTING_COLUMN_ALIASES)

        if date_col is None or price_col is None:
            raise ValueError(
                f"Kon datum- en/of prijskolom niet vinden in {path}. "
                f"Gevonden kolommen: {reader.fieldnames}. "
                f"Verwacht een van {DATE_COLUMN_ALIASES} en een van {PRICE_COLUMN_ALIASES}."
            )

        points: list[OwnPricePoint] = []
        for row_num, row in enumerate(reader, start=2):
            raw_date = row.get(date_col, "")
            raw_price = row.get(price_col, "")
            if not raw_date or not raw_price:
                continue
            try:
                checkin = _parse_date(raw_date)
                price = float(str(raw_price).replace(",", ".").replace("€", "").strip())
            except ValueError as exc:
                raise ValueError(f"Kon rij {row_num} in {path} niet parsen: {exc}") from exc

            listing = row.get(listing_col, "default").strip() if listing_col else "default"
            points.append(OwnPricePoint(listing=listing, checkin_date=checkin, price=price))

        return points
