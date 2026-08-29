"""Relevantiefilter voor gevonden listings tijdens discovery.

Een listing telt alleen mee als concurrent wanneer alle drie criteria
kloppen: capaciteit, type-keyword match, en afstand tot de referentielocatie.
"""

from __future__ import annotations

import math
from dataclasses import dataclass
from typing import Optional


@dataclass
class RelevanceConfig:
    capacity_min: int
    capacity_max: int
    type_keywords: list[str]
    origin_lat: float
    origin_lon: float
    max_distance_km: float


@dataclass
class ListingCandidate:
    name: str
    url: str
    capacity: Optional[int]
    rating: Optional[float]
    price: Optional[float]
    latitude: Optional[float]
    longitude: Optional[float]


def haversine_km(lat1: float, lon1: float, lat2: float, lon2: float) -> float:
    r = 6371.0
    phi1, phi2 = math.radians(lat1), math.radians(lat2)
    dphi = math.radians(lat2 - lat1)
    dlambda = math.radians(lon2 - lon1)
    a = math.sin(dphi / 2) ** 2 + math.cos(phi1) * math.cos(phi2) * math.sin(dlambda / 2) ** 2
    return 2 * r * math.asin(math.sqrt(a))


def capacity_ok(candidate: ListingCandidate, cfg: RelevanceConfig) -> bool:
    if candidate.capacity is None:
        return False
    return cfg.capacity_min <= candidate.capacity <= cfg.capacity_max


def type_ok(candidate: ListingCandidate, cfg: RelevanceConfig) -> bool:
    name_lower = candidate.name.lower()
    return any(keyword.lower() in name_lower for keyword in cfg.type_keywords)


def distance_km(candidate: ListingCandidate, cfg: RelevanceConfig) -> Optional[float]:
    if candidate.latitude is None or candidate.longitude is None:
        return None
    return haversine_km(cfg.origin_lat, cfg.origin_lon, candidate.latitude, candidate.longitude)


def distance_ok(candidate: ListingCandidate, cfg: RelevanceConfig) -> bool:
    dist = distance_km(candidate, cfg)
    if dist is None:
        # Geen coördinaten kunnen ophalen -> niet automatisch afkeuren op
        # afstand, maar ook niet goedkeuren zonder bewijs. We behandelen dit
        # als "onbekend" en laten capacity/type de doorslag geven; de
        # afstand wordt None opgeslagen zodat het bij handmatige review
        # opvalt.
        return True
    return dist <= cfg.max_distance_km


def is_relevant(candidate: ListingCandidate, cfg: RelevanceConfig) -> bool:
    return capacity_ok(candidate, cfg) and type_ok(candidate, cfg) and distance_ok(candidate, cfg)
