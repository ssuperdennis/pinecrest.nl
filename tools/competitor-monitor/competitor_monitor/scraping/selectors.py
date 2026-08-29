"""Centrale plek voor alle Booking.com selectors.

Booking.com wijzigt zijn DOM regelmatig. Als de scraper ineens overal
"kon veld X niet lezen" logt, begin dan hier: open een listing met
`--debug` erbij, bekijk de screenshot/HTML-dump in screenshots/, en
werk de selector hieronder bij. De rest van de code hoeft dan niet aan
te worden gepast.

Selectors zijn zoveel mogelijk gebaseerd op `data-testid` attributen,
omdat die minder vaak wijzigen dan CSS-classnamen. Elke selector heeft
een lijst van alternatieven (van specifiek naar generiek) die na elkaar
geprobeerd worden.
"""

from __future__ import annotations

# --- Zoekresultatenpagina -------------------------------------------------

SEARCH_RESULT_CARD = ['div[data-testid="property-card"]']

RESULT_TITLE = [
    'div[data-testid="title"]',
    'h3[data-testid="title"]',
]

RESULT_URL_ANCHOR = [
    'a[data-testid="title-link"]',
    "a.hotel_name_link",
    "a[href*='/hotel/']",
]

RESULT_CAPACITY = [
    'div[data-testid="property-card-unit-configuration"]',
]

RESULT_RATING = [
    'div[data-testid="review-score"] div.ac4a7896c7',
    'div[data-testid="review-score"]',
]

RESULT_PRICE = [
    'span[data-testid="price-and-discounted-price"]',
    'span[data-testid="availability-rate-price"]',
]

NEXT_PAGE_BUTTON = [
    'button[aria-label="Volgende pagina"]',
    'button[aria-label="Next page"]',
]

# --- Listingpagina (detail) ------------------------------------------------

LISTING_COORDINATES_META = [
    'meta[property="place:location:latitude"]',  # + longitude equivalent
]

LISTING_TITLE = [
    'h2[data-testid="title"]',
    "h2#hp_hotel_name",
]

# Beschikbaarheidskalender: Booking laadt dit meestal via een los
# calendar-endpoint/component per maand. We proberen eerst het
# geïntegreerde prijs-per-nacht widget; valt dat weg dan wordt de
# maandkalender geopend.
AVAILABILITY_CALENDAR_TRIGGER = [
    'button[data-testid="searchbox-dates-container"]',
]

AVAILABILITY_DAY_CELL = [
    'span[data-date]',
    'td[data-date]',
]

AVAILABILITY_DAY_PRICE = [
    'span[data-testid="availability-day-price"]',
]

SOLD_OUT_BANNER = [
    'div[data-testid="unavailability-messages"]',
    "text=Geen kamers beschikbaar",
]
