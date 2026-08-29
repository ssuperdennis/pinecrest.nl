"""Telegram-notificaties voor prijsafwijkingen en gefaalde runs."""

from __future__ import annotations

import logging
import os

import httpx

from .analysis import DateComparison

logger = logging.getLogger("competitor_monitor.notify")

TELEGRAM_API_URL = "https://api.telegram.org/bot{token}/sendMessage"


def _send(token: str, chat_id: str, text: str) -> bool:
    url = TELEGRAM_API_URL.format(token=token)
    try:
        response = httpx.post(
            url,
            json={"chat_id": chat_id, "text": text, "parse_mode": "HTML"},
            timeout=15.0,
        )
        response.raise_for_status()
        return True
    except Exception:
        logger.exception("Telegram-bericht versturen mislukt")
        return False


def notify_deviations(comparisons: list[DateComparison]) -> bool:
    token = os.environ.get("TELEGRAM_BOT_TOKEN")
    chat_id = os.environ.get("TELEGRAM_CHAT_ID")
    if not token or not chat_id:
        logger.warning("TELEGRAM_BOT_TOKEN/TELEGRAM_CHAT_ID niet gezet, sla notificatie over")
        return False

    if not comparisons:
        return True

    lines = ["<b>Prijsafwijking gedetecteerd</b>"]
    for c in comparisons:
        pct = f"{c.deviation_fraction * 100:+.1f}%" if c.deviation_fraction is not None else "n/b"
        mean = f"€{c.competitor_mean:.0f}" if c.competitor_mean else "n/b"
        lines.append(
            f"{c.checkin_date.isoformat()} — {c.own_listing}: €{c.own_price:.0f} "
            f"vs. concurrentie-gemiddelde {mean} ({pct}, n={c.competitor_count})"
        )

    text = "\n".join(lines)
    return _send(token, chat_id, text)


def notify_run_failure(run_type: str, error_detail: str) -> bool:
    token = os.environ.get("TELEGRAM_BOT_TOKEN")
    chat_id = os.environ.get("TELEGRAM_CHAT_ID")
    if not token or not chat_id:
        logger.warning("TELEGRAM_BOT_TOKEN/TELEGRAM_CHAT_ID niet gezet, sla notificatie over")
        return False

    text = f"<b>Competitor monitor: run mislukt</b>\nType: {run_type}\n{error_detail}"
    return _send(token, chat_id, text)
