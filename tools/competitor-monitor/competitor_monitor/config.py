"""Config- en env-loading, en gedeelde logging-setup voor alle entrypoints."""

from __future__ import annotations

import logging
import os
from pathlib import Path

import yaml
from dotenv import load_dotenv

PROJECT_ROOT = Path(__file__).resolve().parent.parent


def load_env() -> None:
    env_path = PROJECT_ROOT / ".env"
    if env_path.exists():
        load_dotenv(env_path)


def load_search_config(path: str | Path | None = None) -> dict:
    load_env()
    config_path = Path(path or os.environ.get("SEARCH_CONFIG_PATH") or PROJECT_ROOT / "config" / "search_config.yaml")
    if not config_path.is_absolute():
        config_path = PROJECT_ROOT / config_path
    with open(config_path, encoding="utf-8") as f:
        return yaml.safe_load(f)


def db_path() -> str:
    return str(PROJECT_ROOT / os.environ.get("DB_PATH", "data/competitors.db"))


def own_prices_csv_path() -> str:
    return str(PROJECT_ROOT / os.environ.get("OWN_PRICES_CSV_PATH", "data/own_prices.csv"))


def screenshot_dir() -> str:
    return str(PROJECT_ROOT / "screenshots")


def setup_logging(run_type: str, debug: bool = False) -> None:
    log_dir = PROJECT_ROOT / "logs"
    log_dir.mkdir(parents=True, exist_ok=True)
    log_file = log_dir / f"{run_type}.log"

    level = logging.DEBUG if debug else logging.INFO
    logging.basicConfig(
        level=level,
        format="%(asctime)s %(levelname)s %(name)s: %(message)s",
        handlers=[
            logging.FileHandler(log_file, encoding="utf-8"),
            logging.StreamHandler(),
        ],
    )
