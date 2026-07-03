<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BrapiService
{
    private PendingRequest $http;

    public function __construct()
    {
        $this->http = Http::baseUrl(config('services.brapi.base_url', 'https://brapi.dev/api'))
            ->withHeaders([
                'Authorization' => 'Bearer ' . config('services.brapi.key'),
            ])
            ->acceptJson()
            ->timeout(10)
            ->retry(2, 500);
    }

    public function fetchQuotes(array $symbols): Collection
    {
        if (empty($symbols)) {
            return collect();
        }

        $symbols = array_map(fn (string $s): string => strtoupper(trim($s)), $symbols);
        $cacheKey = 'brapi_quotes_' . md5(implode(',', $symbols));

        return Cache::remember($cacheKey, 60, function () use ($symbols) {
            try {
                $response = $this->http->get('/v2/stocks/quote', [
                    'symbols' => implode(',', $symbols),
                ]);

                if ($response->failed()) {
                    Log::warning('brapi.dev API error', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);

                    return collect();
                }

                $results = $response->json('results');

                if ($results === null || ! is_array($results)) {
                    Log::warning('brapi.dev unexpected response', [
                        'body' => $response->body(),
                    ]);

                    return collect();
                }

                return collect($results)
                    ->mapWithKeys(function (array $item): array {
                        $data = $item['data'] ?? [];

                        return [$item['symbol'] => [
                            'price' => $data['regularMarketPrice'] ?? null,
                            'logourl' => $data['logourl'] ?? null,
                            'change' => $data['regularMarketChange'] ?? null,
                            'change_percent' => $data['regularMarketChangePercent'] ?? null,
                            'market_cap' => $data['marketCap'] ?? null,
                            'volume' => $data['regularMarketVolume'] ?? null,
                        ]];
                    });
            } catch (\Throwable $e) {
                Log::error('brapi.dev connection error: ' . $e->getMessage());

                return collect();
            }
        });
    }
}
