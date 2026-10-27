# Metal Sentinel API

[![Run in Postman](https://run.pstmn.io/button.svg)](https://app.getpostman.com/run-collection/703795-79e88024-52c6-4fa7-bdbb-c83bc9d4236a)

Live and historical prices for ten precious and base metals, gold karat and silver fineness prices, forex and crypto quotes, market status and mining stocks, through one REST API.

- **Metals:** gold (AU), silver (AG), platinum (PT), palladium (PD), rhodium (RH), copper (CU), nickel (NI), aluminum (AL), lead (PB), zinc (ZN)
- **Currencies:** 180+ fiat and crypto
- **Refresh:** spot quotes every 15 seconds from a primary market feed
- **Free plan:** 7,500 requests a month on RapidAPI, no credit card, same data as paid plans
- **Marketplaces:** subscribe on [RapidAPI](https://rapidapi.com/ghostsvos/api/metal-sentinel) or [Zyla API Hub](https://zylalabs.com/api-marketplace/other/precious+and+base+metals+price+api/14542); same data and parameters on both (the two crypto endpoints are on RapidAPI only)

| | |
|---|---|
| Get an API key | [RapidAPI](https://rapidapi.com/ghostsvos/api/metal-sentinel) or [Zyla API Hub](https://zylalabs.com/api-marketplace/other/precious+and+base+metals+price+api/14542) |
| Endpoint reference | https://metal-sentinel.com/endpoints (Zyla URLs: https://metal-sentinel.com/endpoints?provider=zyla) |
| Pricing | https://metal-sentinel.com/pricing |
| OpenAPI 3.1 spec | [`openapi.json`](openapi.json) (also served at https://metal-sentinel.com/openapi.json) |
| Postman collection | [`postman/metal-sentinel.postman_collection.json`](postman/metal-sentinel.postman_collection.json), or open the [public Postman workspace](https://www.postman.com/mycrofts-team/metal-sentinel/overview) |
| Free embeddable widgets | https://metal-sentinel.com/widgets |

## Quick start

### RapidAPI

Every request needs two headers from RapidAPI:

```bash
curl "https://metal-sentinel.p.rapidapi.com/api/metal-quote?symbol=AU&currency=USD" \
  -H "x-rapidapi-host: metal-sentinel.p.rapidapi.com" \
  -H "x-rapidapi-key: YOUR_KEY"
```

### Zyla API Hub

Zyla gives each endpoint its own URL under `https://zylalabs.com/api/14542/precious+and+base+metals+price+api/` and authenticates with a bearer token. Query parameters are the same as on RapidAPI:

```bash
curl "https://zylalabs.com/api/14542/precious+and+base+metals+price+api/37151/current+spot+price+for+one+metal+in+one+currency?symbol=AU&currency=USD" \
  -H "Authorization: Bearer YOUR_ZYLA_API_KEY"
```

The Zyla URL for every endpoint is in the [Endpoints](#endpoints) table below and on https://metal-sentinel.com/endpoints?provider=zyla.

### Response

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

Each sample is a single-file client with a runnable `main`. The samples call the RapidAPI gateway; set `METAL_SENTINEL_KEY` to your RapidAPI key first.

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

| Endpoint (RapidAPI) | Zyla path | Returns |
|---|---|---|
| `GET /api/metal-quote?symbol=AU&currency=USD` | `37151/current+spot+price+for+one+metal+in+one+currency` | Current spot price for one metal |
| `GET /api/gold-price?currency=USD` | `37152/gold+price+with+karat+values` | Gold spot price with 24k, 18k, 14k, 9k karat prices |
| `GET /api/silver-price?currency=USD` | `37153/silver+price+with+fineness+values` | Silver spot price with 999, 958, 925, 900, 800 fineness prices |
| `GET /api/metal-history?symbol=AU&currency=USD&startTime=…&endTime=…` | `37154/daily+historical+prices+between+two+timestamps` | Daily prices between two timestamps (max 30 per call) |
| `GET /api/historical-points?symbol=AU&currency=USD&timestamp=…` | `37155/historical+price+snapshots` | Snapshots now, 30d, 60d, 1y and 5y before a timestamp |
| `GET /api/crypto-quote?symbol=BTC&currency=USD` | RapidAPI only | Crypto quote with 24h comparison |
| `GET /api/forex-quote?symbol=EUR` | `37156/foreign+exchange+rate+against+usd` | FX rate against USD |
| `GET /api/get-stock` | `37157/live+mining+and+commodity+equities` | Mining and commodity equities |
| `GET /api/market-status` | `37158/whether+the+metals+market+is+open+and+when+it+next+changes` | Open or closed, and the next change |
| `GET /api/allowed-metals` | `37159/supported+metal+symbols` | Supported metal symbols |
| `GET /api/allowed-currencies` | `37160/supported+fiat+currency+codes` | Supported fiat codes |
| `GET /api/allowed-cryptos` | RapidAPI only | Supported crypto symbols |

On Zyla, prefix the Zyla path with `https://zylalabs.com/api/14542/precious+and+base+metals+price+api/` and send the same query parameters. Full parameters and response schemas are in [`openapi.json`](openapi.json).

## Errors

Validation failures and upstream feed errors return HTTP 200 with an `error` field:

```json
{ "error": "The symbol field is required." }
```

The samples above raise on that field so you never silently read a missing price.

## Postman

The collection is published on the [Postman API Network](https://www.postman.com/mycrofts-team/metal-sentinel/overview): fork it into your own workspace, set the `rapidapi_key` collection variable, and every endpoint is pre-filled with example parameters. The same file is in `postman/` if you prefer to import it by hand.

## Contributing

Issues and pull requests are welcome for the samples and the collection. The spec is generated from the API itself; report mismatches as issues and they will be fixed at the source.

## License

The samples and collection are MIT licensed. Use of the API is governed by the [terms of service](https://metal-sentinel.com/terms).
