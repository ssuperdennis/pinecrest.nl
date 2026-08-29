# Competitor Price Monitor (Tiny Bos Lodge)

Los Python-project dat concurrerende vakantieaccommodaties op Booking.com
rond Wenum-Wiesel/Veluwe volgt: eenmalig/wekelijks nieuwe concurrenten
ontdekken, dagelijks hun prijs/beschikbaarheid scrapen, en de eigen prijzen
(Hostaway-export) daartegen afzetten met een Telegram-waarschuwing bij >15%
afwijking.

Staat los van de PHP-website in de rest van deze repo (andere taal, eigen
dependencies, eigen scheduling).

## Belangrijk: Booking.com en scraping

Booking.com's gebruiksvoorwaarden staan geautomatiseerd ophalen van data
niet toe. Dit script is bedoeld voor eigen, beperkt gebruik (concurrentie
rond twee huisjes), met ingebouwde throttling om de kans op blokkades te
verkleinen — maar een blokkade van je IP is altijd mogelijk. Gebruik op
eigen verantwoordelijkheid en overweeg de scrape-frequentie laag te houden.

## Installatie

```bash
cd tools/competitor-monitor
python3 -m venv venv
source venv/bin/activate
pip install -r requirements.txt
playwright install chromium

cp config/.env.example .env
# vul TELEGRAM_BOT_TOKEN en TELEGRAM_CHAT_ID in .env in
```

## Configuratie

- `config/search_config.yaml` — zoektermen, regio, relevantiefilters
  (capaciteit, type-keywords, straal), scraping-instellingen (throttling,
  headless, timeouts), monitor-horizon en de 15%-afwijkingsdrempel.
- `.env` — Telegram-credentials en bestandspaden (zie `.env.example`).

## Gebruik

**Stap 1 — concurrentieonderzoek** (eenmalig, daarna wekelijks via cron):

```bash
python scripts/run_discovery.py --debug
```

Doorloopt de zoektermen uit de config, filtert op capaciteit (2-6
personen), type (tiny house/lodge/vakantiehuisje/…) en afstand (≤30km tot
Wenum-Wiesel), en zet relevante listings in de `competitors`-tabel.
Concurrenten die niet meer teruggevonden worden, gaan na 2 gemiste runs
automatisch op inactief (geschiedenis blijft bewaard, er wordt nooit iets
verwijderd).

Bekijk en corrigeer daarna false positives:

```bash
python scripts/manage_competitors.py list
python scripts/manage_competitors.py ignore 7   # false positive uitzetten
python scripts/manage_competitors.py unignore 7
```

**Stap 2 — dagelijkse prijs/beschikbaarheid**:

```bash
python scripts/run_monitor.py --debug
```

Haalt voor elke actieve, niet-genegeerde concurrent de prijs en
beschikbaarheid op voor de komende `monitor_horizon_days` dagen (default
60) en slaat dit op als snapshot in `price_snapshots`.

**Stap 4/5 — analyse en Telegram-waarschuwing**:

```bash
python scripts/run_analysis.py --csv data/own_prices.csv
```

Verwacht een Hostaway CSV-export met een datum- en prijskolom (en
optioneel een listingnaam-kolom voor de twee huisjes apart, zie
`competitor_monitor/own_prices.py` voor de herkende kolomnamen).
Berekent per datum het gemiddelde/mediaan van actieve concurrenten,
vergelijkt met de eigen prijs, en stuurt een Telegram-bericht bij >15%
afwijking (drempel instelbaar via `deviation_threshold` in de config).

## Debuggen van mislukte scrapes

Booking.com's HTML/DOM verandert regelmatig. Alle selectors staan
centraal in `competitor_monitor/scraping/selectors.py` — begin daar als
logs veel "kon veld X niet lezen"-meldingen tonen.

Met `--debug` wordt bij een mislukte parse automatisch een screenshot +
HTML-dump weggeschreven naar `screenshots/`, zodat je kunt zien wat er
op de pagina stond en de selector kunt bijwerken.

## Scheduling

Zie `crontab.example` voor een kant-en-klare crontab: stap 2 (monitor) +
analyse dagelijks, stap 1 (discovery) wekelijks. Elke run wordt gelogd in
`logs/<run_type>.log` én in de `run_log`-tabel in de database; een
mislukte run stuurt automatisch een Telegram-bericht.

## Database

SQLite, standaard `data/competitors.db` (pad instelbaar via `.env`).

- `competitors` — ontdekte concurrenten met status (`active`,
  `ignored_manually`), afstand, capaciteit, rating.
- `price_snapshots` — dagelijkse prijs/beschikbaarheid per concurrent per
  check-indatum, met tijdstip van scrapen (zo blijft trend-historie
  bewaard).
- `run_log` — start/eind/status van elke discovery/monitor/analysis-run.
