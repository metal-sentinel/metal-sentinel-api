# Metal Sentinel API

Live and historical prices for ten precious and base metals, gold karat and silver fineness prices, forex and crypto quotes, market status and mining stocks, through one REST API.

- **Metals:** gold (AU), silver (AG), platinum (PT), palladium (PD), rhodium (RH), copper (CU), nickel (NI), aluminum (AL), lead (PB), zinc (ZN)
- **Currencies:** 180+ fiat and crypto
- **Refresh:** spot quotes every 15 seconds from a primary market feed
- **Free plan:** 7,500 requests a month, no credit card, same data as paid plans

| | |
|---|---|
| Get an API key | https://rapidapi.com/ghostsvos/api/metal-sentinel |
| Endpoint reference | https://metal-sentinel.com/endpoints |
| Pricing | https://metal-sentinel.com/pricing |
| OpenAPI 3.1 spec | [`openapi.json`](openapi.json) (also served at https://metal-sentinel.com/openapi.json) |
| Postman collection | [`postman/metal-sentinel.postman_collection.json`](postman/metal-sentinel.postman_collection.json) |
| Free embeddable widgets | https://metal-sentinel.com/widgets |

## Quick start

Every request needs two headers from RapidAPI:

```bash
curl "https://metal-sentinel.p.rapidapi.com/api/metal-quote?symbol=AU&currency=USD" \
  -H "x-rapidapi-host: metal-sentinel.p.rapidapi.com" \
  -H "x-rapidapi-key: YOUR_KEY"
```

```json
{
  "ID": 0,
  "results": [{
    "symbol": "AU", "currency": "USD",
    "ask": 4126.2, "mid": 4125.65, "bid": 4125.1,
    "high": 4140.3, "low": 4098.7, "open": 4110.0, "close": 4108.2,
    "timestamp": 1760000000, "change": 17.45, "changePercentage": 0.42,
    "unit": "OUNCE", "originalTime": "2025-10-09T08:53:20Z"
  }]
}
```

Prices are per unit in the requested currency. `unit` is `OUNCE` (troy ounce) for precious metals; base metals state their own unit. `timestamp` is Unix seconds.

## Code samples

Each sample is a single-file client with a runnable `main`. Set `METAL_SENTINEL_KEY` to your RapidAPI key first.

| Language | File | Run |
|---|---|---|
| Python 3.9+ | [`examples/python/metal_sentinel.py`](examples/python/metal_sentinel.py) | `pip install -r requirements.txt && python metal_sentinel.py` |
| Node 18+ | [`examples/node/metal-sentinel.mjs`](examples/node/metal-sentinel.mjs) | `node metal-sentinel.mjs` |
| PHP 8.1+ | [`examples/php/metal-sentinel.php`](examples/php/metal-sentinel.php) | `php metal-sentinel.php` |

Python:

```python
from metal_sentinel import MetalSentinel

client = MetalSentinel()  # reads METAL_SENTINEL_KEY
gold = client.gold_price("EUR")
print(gold["mid"], gold["karat_prices"]["18k"])
```

Node:

```js
import { MetalSentinel } from './metal-sentinel.mjs';

const client = new MetalSentinel();
const silver = await client.silverPrice('USD');
console.log(silver.mid, silver.fineness_prices['925']);
```

PHP:

```php
$client = new MetalSentinel(getenv('METAL_SENTINEL_KEY'));
$copper = $client->metalQuote('CU', 'USD');
echo $copper['mid'], ' per ', $copper['unit'];
```

## Endpoints

| Endpoint | Returns |
|---|---|
| `GET /api/metal-quote?symbol=AU&currency=USD` | Current spot price for one metal |
| `GET /api/gold-price?currency=USD` | Gold spot price with 24k, 18k, 14k, 9k karat prices |
| `GET /api/silver-price?currency=USD` | Silver spot price with 999, 958, 925, 900, 800 fineness prices |
| `GET /api/metal-history?symbol=AU&currency=USD&startTime=…&endTime=…` | Daily prices between two timestamps (max 30 per call) |
| `GET /api/historical-points?symbol=AU&currency=USD&timestamp=…` | Snapshots now, 30d, 60d, 1y and 5y before a timestamp |
| `GET /api/crypto-quote?symbol=BTC&currency=USD` | Crypto quote with 24h comparison |
| `GET /api/forex-quote?symbol=EUR` | FX rate against USD |
| `GET /api/get-stock` | Mining and commodity equities |
| `GET /api/market-status` | Open or closed, and the next change |
| `GET /api/allowed-metals` | Supported metal symbols |
| `GET /api/allowed-currencies` | Supported fiat codes |
| `GET /api/allowed-cryptos` | Supported crypto symbols |

Full parameters and response schemas are in [`openapi.json`](openapi.json).

## Errors

Validation failures and upstream feed errors return HTTP 200 with an `error` field:

```json
{ "error": "The symbol field is required." }
```

The samples above raise on that field so you never silently read a missing price.

## Postman

Import `postman/metal-sentinel.postman_collection.json`, then set the `rapidapi_key` collection variable. Every endpoint is pre-filled with example parameters.

## Contributing

Issues and pull requests are welcome for the samples and the collection. The spec is generated from the API itself; report mismatches as issues and they will be fixed at the source.

## License

The samples and collection are MIT licensed. Use of the API is governed by the [terms of service](https://metal-sentinel.com/terms).
