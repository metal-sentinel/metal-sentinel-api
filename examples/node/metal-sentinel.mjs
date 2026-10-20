// Minimal Metal Sentinel client for Node 18+ (native fetch, ESM).
//
//   METAL_SENTINEL_KEY=your_key node metal-sentinel.mjs
//
// Docs: https://metal-sentinel.com/endpoints

const BASE_URL = 'https://metal-sentinel.p.rapidapi.com';
const HOST = 'metal-sentinel.p.rapidapi.com';

export class MetalSentinel {
  constructor(apiKey = process.env.METAL_SENTINEL_KEY) {
    if (!apiKey) throw new Error('Set METAL_SENTINEL_KEY or pass an API key');
    this.headers = { 'x-rapidapi-key': apiKey, 'x-rapidapi-host': HOST };
  }

  async #get(path, params = {}) {
    const url = new URL(path, BASE_URL);
    for (const [key, value] of Object.entries(params)) url.searchParams.set(key, String(value));
    const response = await fetch(url, { headers: this.headers });
    if (!response.ok) throw new Error(`HTTP ${response.status} for ${path}`);
    const data = await response.json();
    if (data && typeof data === 'object' && 'error' in data) throw new Error(data.error);
    return data;
  }

  /** Current spot price for one metal. Symbols: AU AG PT PD RH CU NI AL PB ZN. */
  async metalQuote(symbol = 'AU', currency = 'USD') {
    return (await this.#get('/api/metal-quote', { symbol, currency })).results[0];
  }

  /** Gold spot price plus 24k, 18k, 14k and 9k karat prices. */
  async goldPrice(currency = 'USD') {
    return (await this.#get('/api/gold-price', { currency })).results[0];
  }

  /** Silver spot price plus fineness prices (999, 958, 925, 900, 800). */
  async silverPrice(currency = 'USD') {
    return (await this.#get('/api/silver-price', { currency })).results[0];
  }

  /** Daily prices for the last `days` days (max 30 per call). */
  async metalHistory(symbol, currency = 'USD', days = 30) {
    const endTime = Math.floor(Date.now() / 1000);
    const startTime = endTime - days * 86_400;
    return (await this.#get('/api/metal-history', { symbol, currency, startTime, endTime, limit: Math.min(days, 30) })).results;
  }

  async marketStatus() {
    return this.#get('/api/market-status');
  }
}

if (import.meta.url === `file://${process.argv[1]}`) {
  const client = new MetalSentinel();

  const gold = await client.goldPrice('USD');
  console.log(`Gold: ${gold.mid.toFixed(2)} USD per troy ounce (18k: ${gold.karat_prices['18k'].toFixed(2)})`);

  const copper = await client.metalQuote('CU', 'EUR');
  console.log(`Copper: ${copper.mid.toFixed(4)} EUR per ${copper.unit.toLowerCase()}`);

  for (const point of await client.metalHistory('AG', 'USD', 7)) {
    console.log(`  ${new Date(point.timestamp * 1000).toISOString().slice(0, 10)}  silver close ${point.close.toFixed(2)}`);
  }

  console.log('Market:', (await client.marketStatus()).status);
}
