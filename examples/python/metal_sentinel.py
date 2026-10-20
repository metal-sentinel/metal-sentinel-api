"""Minimal Metal Sentinel client for Python 3.9+.

Set METAL_SENTINEL_KEY to your RapidAPI key, then run:

    python metal_sentinel.py

Docs: https://metal-sentinel.com/endpoints
"""

from __future__ import annotations

import os
import time
from typing import Any

import requests

BASE_URL = "https://metal-sentinel.p.rapidapi.com"
HOST = "metal-sentinel.p.rapidapi.com"


class MetalSentinel:
    def __init__(self, api_key: str | None = None, timeout: float = 10.0) -> None:
        self.api_key = api_key or os.environ["METAL_SENTINEL_KEY"]
        self.timeout = timeout
        self.session = requests.Session()
        self.session.headers.update({"x-rapidapi-key": self.api_key, "x-rapidapi-host": HOST})

    def _get(self, path: str, **params: Any) -> Any:
        response = self.session.get(f"{BASE_URL}{path}", params=params, timeout=self.timeout)
        response.raise_for_status()
        data = response.json()
        if isinstance(data, dict) and "error" in data:
            raise RuntimeError(data["error"])
        return data

    def metal_quote(self, symbol: str = "AU", currency: str = "USD") -> dict:
        """Current spot price for one metal. Symbols: AU AG PT PD RH CU NI AL PB ZN."""
        return self._get("/api/metal-quote", symbol=symbol, currency=currency)["results"][0]

    def gold_price(self, currency: str = "USD") -> dict:
        """Gold spot price plus 24k, 18k, 14k and 9k karat prices."""
        return self._get("/api/gold-price", currency=currency)["results"][0]

    def silver_price(self, currency: str = "USD") -> dict:
        """Silver spot price plus 999, 958, 925, 900 and 800 fineness prices."""
        return self._get("/api/silver-price", currency=currency)["results"][0]

    def metal_history(self, symbol: str, currency: str = "USD", days: int = 30) -> list[dict]:
        """Daily prices for the last `days` days (max 30 per call)."""
        end = int(time.time())
        start = end - days * 86_400
        return self._get("/api/metal-history", symbol=symbol, currency=currency, startTime=start, endTime=end, limit=min(days, 30))["results"]

    def market_status(self) -> dict:
        return self._get("/api/market-status")


if __name__ == "__main__":
    client = MetalSentinel()

    gold = client.gold_price("USD")
    print(f"Gold: {gold['mid']:.2f} USD per troy ounce (18k: {gold['karat_prices']['18k']:.2f})")

    copper = client.metal_quote("CU", "EUR")
    print(f"Copper: {copper['mid']:.4f} EUR per {copper['unit'].lower()}")

    history = client.metal_history("AG", "USD", days=7)
    for point in history:
        print(f"  {time.strftime('%Y-%m-%d', time.gmtime(point['timestamp']))}  silver close {point['close']:.2f}")

    print("Market:", client.market_status()["status"])
