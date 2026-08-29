"""Stap 4: analyse - concurrentprijzen samenvatten en vergelijken met eigen prijzen."""

from __future__ import annotations

import statistics
from dataclasses import dataclass
from datetime import date

from . import db
from .own_prices import OwnPricePoint


@dataclass
class DateComparison:
    checkin_date: date
    competitor_mean: float | None
    competitor_median: float | None
    competitor_count: int
    own_listing: str
    own_price: float
    deviation_fraction: float | None  # (own - mean) / mean
    flagged: bool


def competitor_stats_for_date(conn, checkin_date: date) -> tuple[float | None, float | None, int]:
    rows = db.latest_snapshots_for_date(conn, checkin_date)
    prices = [r["price_per_night"] for r in rows if r["available"] and r["price_per_night"] is not None]
    if not prices:
        return None, None, 0
    return statistics.mean(prices), statistics.median(prices), len(prices)


def compare_own_prices(
    conn,
    own_prices: list[OwnPricePoint],
    deviation_threshold: float,
) -> list[DateComparison]:
    results: list[DateComparison] = []
    for point in own_prices:
        mean_price, median_price, count = competitor_stats_for_date(conn, point.checkin_date)

        deviation = None
        flagged = False
        if mean_price:
            deviation = (point.price - mean_price) / mean_price
            flagged = abs(deviation) > deviation_threshold

        results.append(
            DateComparison(
                checkin_date=point.checkin_date,
                competitor_mean=mean_price,
                competitor_median=median_price,
                competitor_count=count,
                own_listing=point.listing,
                own_price=point.price,
                deviation_fraction=deviation,
                flagged=flagged,
            )
        )
    return results


def flagged_only(comparisons: list[DateComparison]) -> list[DateComparison]:
    return [c for c in comparisons if c.flagged]
