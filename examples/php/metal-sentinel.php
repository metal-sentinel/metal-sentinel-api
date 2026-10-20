<?php

/**
 * Minimal Metal Sentinel client for PHP 8.1+ using the curl extension.
 *
 *   METAL_SENTINEL_KEY=your_key php metal-sentinel.php
 *
 * Docs: https://metal-sentinel.com/endpoints
 */

declare(strict_types=1);

final class MetalSentinel
{
    private const BASE_URL = 'https://metal-sentinel.p.rapidapi.com';
    private const HOST = 'metal-sentinel.p.rapidapi.com';

    public function __construct(private readonly string $apiKey) {}

    /** @return array<string, mixed> */
    private function get(string $path, array $params = []): array
    {
        $url = self::BASE_URL.$path.($params ? '?'.http_build_query($params) : '');
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => [
                'x-rapidapi-key: '.$this->apiKey,
                'x-rapidapi-host: '.self::HOST,
            ],
        ]);
        $body = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        curl_close($curl);

        if ($body === false || $status >= 400) {
            throw new RuntimeException("HTTP {$status} for {$path}");
        }

        $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        if (is_array($data) && isset($data['error'])) {
            throw new RuntimeException($data['error']);
        }

        return $data;
    }

    /** Current spot price for one metal. Symbols: AU AG PT PD RH CU NI AL PB ZN. */
    public function metalQuote(string $symbol = 'AU', string $currency = 'USD'): array
    {
        return $this->get('/api/metal-quote', ['symbol' => $symbol, 'currency' => $currency])['results'][0];
    }

    /** Gold spot price plus 24k, 18k, 14k and 9k karat prices. */
    public function goldPrice(string $currency = 'USD'): array
    {
        return $this->get('/api/gold-price', ['currency' => $currency])['results'][0];
    }

    /** Silver spot price plus fineness prices (999, 958, 925, 900, 800). */
    public function silverPrice(string $currency = 'USD'): array
    {
        return $this->get('/api/silver-price', ['currency' => $currency])['results'][0];
    }

    /** Daily prices for the last $days days (max 30 per call). */
    public function metalHistory(string $symbol, string $currency = 'USD', int $days = 30): array
    {
        $end = time();

        return $this->get('/api/metal-history', [
            'symbol' => $symbol,
            'currency' => $currency,
            'startTime' => $end - $days * 86400,
            'endTime' => $end,
            'limit' => min($days, 30),
        ])['results'];
    }

    public function marketStatus(): array
    {
        return $this->get('/api/market-status');
    }
}

if (PHP_SAPI === 'cli' && realpath($argv[0] ?? '') === __FILE__) {
    $client = new MetalSentinel(getenv('METAL_SENTINEL_KEY') ?: throw new RuntimeException('Set METAL_SENTINEL_KEY'));

    $gold = $client->goldPrice('USD');
    printf("Gold: %.2f USD per troy ounce (18k: %.2f)\n", $gold['mid'], $gold['karat_prices']['18k']);

    $copper = $client->metalQuote('CU', 'EUR');
    printf("Copper: %.4f EUR per %s\n", $copper['mid'], strtolower($copper['unit']));

    foreach ($client->metalHistory('AG', 'USD', 7) as $point) {
        printf("  %s  silver close %.2f\n", gmdate('Y-m-d', $point['timestamp']), $point['close']);
    }

    echo 'Market: ', $client->marketStatus()['status'], PHP_EOL;
}
