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
                'Authorization' => 'Bearer '.config('services.brapi.key'),
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
        $cacheKey = 'brapi_quotes_'.md5(implode(',', $symbols));

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
                Log::error('brapi.dev connection error: '.$e->getMessage());

                return collect();
            }
        });
    }

    public function fetchCompleteQuote(string $symbol): ?array
    {
        $symbol = strtoupper(trim($symbol));
        $cacheKey = 'brapi_complete_'.$symbol;

        return Cache::remember($cacheKey, 60, function () use ($symbol) {
            try {
                $quote = $this->fetchQuote($symbol);
                if ($quote === null) {
                    return null;
                }

                if ($this->isFiiTicker($symbol)) {
                    $fiiIndicators = $this->fetchFiiIndicators($symbol);
                    if ($fiiIndicators !== null) {
                        return $this->buildFiiData($symbol, $quote, $fiiIndicators);
                    }
                }

                return $this->buildStockData($symbol, $quote);
            } catch (\Throwable $e) {
                Log::error('brapi.dev complete quote error: '.$e->getMessage());

                return null;
            }
        });
    }

    private function decimalToPercent(array $data, string $key): ?float
    {
        return isset($data[$key]) && $data[$key] !== null
            ? (float) $data[$key] * 100
            : null;
    }

    private function isFiiTicker(string $symbol): bool
    {
        return (bool) preg_match('/11$/', $symbol);
    }

    private function buildStockData(string $symbol, array $quote): array
    {
        $stats = $this->fetchStatistics($symbol);
        $financials = $this->fetchFinancialData($symbol);
        $profile = $this->fetchProfile($symbol);

        $s = $stats ?? [];
        $f = $financials ?? [];
        $p = $profile ?? [];

        $ebitda = $f['ebitda'] ?? null;
        $totalDebt = $f['totalDebt'] ?? null;
        $totalCash = $f['totalCash'] ?? null;

        $netDebt = ($totalDebt !== null && $totalCash !== null)
            ? $totalDebt - $totalCash
            : null;

        $rawData = [
            'ticker' => $s['symbol'] ?? $quote['symbol'] ?? $symbol,
            'name' => $p['name'] ?? $quote['longName'] ?? $quote['shortName'] ?? $symbol,
            'current_price' => $quote['regularMarketPrice'] ?? $f['currentPrice'] ?? null,
            'market_cap' => $s['marketCap'] ?? $quote['marketCap'] ?? null,
            'enterprise_value' => $s['enterpriseValue'] ?? null,
            'volume_avg_30d' => $quote['regularMarketVolume'] ?? null,
            'dividend_yield' => $this->decimalToPercent($s, 'dividendYield'),
            'price_to_earnings' => $s['trailingPE'] ?? $s['forwardPE'] ?? null,
            'price_to_book' => $s['priceToBook'] ?? null,
            'ev_to_ebitda' => $s['enterpriseToEbitda'] ?? null,
            'price_to_sales' => $s['enterpriseToRevenue'] ?? null,
            'price_to_assets' => null,
            'price_to_cash_flow' => null,
            'roe' => $this->decimalToPercent($s, 'returnOnEquity')
                ?? $this->decimalToPercent($f, 'returnOnEquity'),
            'roa' => $this->decimalToPercent($f, 'returnOnAssets'),
            'profit_margin' => $this->decimalToPercent($f, 'profitMargins'),
            'ebitda_margin' => $this->decimalToPercent($f, 'ebitdaMargins'),
            'gross_margin' => $this->decimalToPercent($f, 'grossMargins'),
            'ebitda' => $ebitda,
            'net_debt' => $netDebt,
            'gross_debt' => $totalDebt,
            'debt_to_ebitda' => ($totalDebt !== null && $ebitda !== null && $ebitda != 0)
                ? round($totalDebt / $ebitda, 4) : null,
            'net_debt_to_ebitda' => ($netDebt !== null && $ebitda !== null && $ebitda != 0)
                ? round($netDebt / $ebitda, 4) : null,
            'current_liquidity' => $f['currentRatio'] ?? null,
            'payout' => $this->decimalToPercent($s, 'payoutRatio'),
            'net_income' => $s['netIncomeToCommon'] ?? null,
            'revenue' => $f['totalRevenue'] ?? null,
            'free_cash_flow' => $f['freeCashflow'] ?? null,
            'dividends_per_share' => $s['lastDividendValue'] ?? null,
            'earnings_per_share' => $s['trailingEps'] ?? $s['earningsPerShare'] ?? null,
            'book_value_per_share' => $s['bookValue'] ?? $f['revenuePerShare'] ?? null,
            'total_shares' => $s['sharesOutstanding'] ?? null,
            'sector' => $p['sector'] ?? null,
            'industry' => $p['industry'] ?? $p['industryDisp'] ?? null,
            'logourl' => $quote['logourl'] ?? $p['logoUrl'] ?? null,
            'long_business_summary' => $p['longBusinessSummary'] ?? null,
            'website' => $p['website'] ?? null,
            'full_time_employees' => $p['fullTimeEmployees'] ?? null,
            'asset_type' => $this->detectAssetType($symbol),
        ];

        // Normaliza setor e nome
        $sector = SectorMapper::normalize($rawData['sector'], $rawData['industry']);
        $normalizedName = NameNormalizer::normalize($symbol, $rawData['name'] ?? null);

        $rawData['sector'] = $sector['sector'] ?? $rawData['sector'];
        $rawData['subsector'] = $sector['subsector'] ?? $rawData['industry'];
        if ($normalizedName !== null) {
            $rawData['name'] = $normalizedName;
        }

        return $rawData;
    }

    private function buildFiiData(string $symbol, array $quote, array $fiiIndicators): array
    {
        $profile = $this->fetchProfile($symbol);

        $f = $fiiIndicators;
        $p = $profile ?? [];

        $dividendYield = null;
        if (isset($f['dividendYield12m']) && $f['dividendYield12m'] !== null) {
            $dividendYield = (float) $f['dividendYield12m'] * 100;
        }

        $dps = null;
        if (isset($f['dividendYield1m']) && $f['dividendYield1m'] !== null && isset($f['price']) && $f['price'] !== null) {
            $dps = $f['dividendYield1m'] * $f['price'];
        }

        $rawData = [
            'ticker' => $f['symbol'] ?? $quote['symbol'] ?? $symbol,
            'name' => $f['name'] ?? $quote['longName'] ?? $quote['shortName'] ?? $symbol,
            'current_price' => $f['price'] ?? $quote['regularMarketPrice'] ?? null,
            'market_cap' => isset($f['sharesOutstanding']) && $f['sharesOutstanding'] !== null && isset($f['price']) && $f['price'] !== null
                ? $f['sharesOutstanding'] * $f['price'] : null,
            'enterprise_value' => null,
            'volume_avg_30d' => $quote['regularMarketVolume'] ?? null,
            'dividend_yield' => $dividendYield,
            'price_to_earnings' => null,
            'price_to_book' => $f['priceToNav'] ?? null,
            'ev_to_ebitda' => null,
            'price_to_sales' => null,
            'price_to_assets' => null,
            'price_to_cash_flow' => null,
            'roe' => null,
            'roa' => null,
            'profit_margin' => null,
            'ebitda_margin' => null,
            'gross_margin' => null,
            'debt_to_ebitda' => null,
            'net_debt_to_ebitda' => null,
            'current_liquidity' => null,
            'payout' => null,
            'net_income' => null,
            'revenue' => null,
            'free_cash_flow' => null,
            'dividends_per_share' => $dps,
            'earnings_per_share' => null,
            'book_value_per_share' => $f['navPerShare'] ?? null,
            'total_shares' => $f['sharesOutstanding'] ?? null,
            'sector' => $p['sector'] ?? $f['segmentType'] ?? 'FII',
            'industry' => $f['segmentoAtuacao'] ?? $p['industry'] ?? null,
            'logourl' => $quote['logourl'] ?? $p['logoUrl'] ?? null,
            'asset_type' => 'fii',
        ];

        // Normaliza segmento FII e nome
        $fii = FiiSegmentMapper::normalize(
            $rawData['sector'],
            $rawData['industry'],
            'fii'
        );
        $normalizedName = NameNormalizer::normalize($symbol, $rawData['name'] ?? null);

        $rawData['sector'] = $fii['sector'] ?? $rawData['sector'];
        $rawData['subsector'] = $fii['subsector'] ?? $rawData['industry'];
        $rawData['segment'] = $fii['segment'] ?? $rawData['sector'];
        if ($normalizedName !== null) {
            $rawData['name'] = $normalizedName;
        }

        return $rawData;
    }

    private function fetchQuote(string $symbol): ?array
    {
        try {
            $response = $this->http->get('/v2/stocks/quote', ['symbols' => $symbol]);
            if ($response->status() === 403) {
                return null;
            }
            if ($response->failed()) {
                return null;
            }
            $results = $response->json('results');
            if (empty($results)) {
                return null;
            }
            $item = $results[0];
            $d = $item['data'] ?? [];
            $d['symbol'] = $item['symbol'] ?? $symbol;

            return $d;
        } catch (\Throwable $e) {
            Log::warning("brapi quote error {$symbol}: {$e->getMessage()}");

            return null;
        }
    }

    private function fetchStatistics(string $symbol): ?array
    {
        try {
            $response = $this->http->get('/v2/stocks/statistics', [
                'symbols' => $symbol,
                'mode' => 'current',
            ]);
            if ($response->status() === 403) {
                return null;
            }
            if ($response->failed()) {
                return null;
            }
            $results = $response->json('results');

            return $results[0]['data'] ?? null;
        } catch (\Throwable $e) {
            Log::warning("brapi stats error {$symbol}: {$e->getMessage()}");

            return null;
        }
    }

    private function fetchFinancialData(string $symbol): ?array
    {
        try {
            $response = $this->http->get('/v2/stocks/financial-data', [
                'symbols' => $symbol,
                'mode' => 'current',
            ]);
            if ($response->status() === 403) {
                return null;
            }
            if ($response->failed()) {
                return null;
            }
            $results = $response->json('results');

            return $results[0]['data'] ?? null;
        } catch (\Throwable $e) {
            Log::warning("brapi financial data error {$symbol}: {$e->getMessage()}");

            return null;
        }
    }

    private function fetchProfile(string $symbol): ?array
    {
        try {
            $response = $this->http->get('/v2/stocks/profile', ['symbols' => $symbol]);
            if ($response->status() === 403) {
                return null;
            }
            if ($response->failed()) {
                return null;
            }
            $results = $response->json('results');

            return $results[0]['data'] ?? null;
        } catch (\Throwable $e) {
            Log::warning("brapi profile error {$symbol}: {$e->getMessage()}");

            return null;
        }
    }

    public function fetchFiiIndicators(string $symbol): ?array
    {
        $symbol = strtoupper(trim($symbol));
        $cacheKey = 'brapi_fii_indicators_'.$symbol;

        return Cache::remember($cacheKey, 360, function () use ($symbol) {
            try {
                $response = $this->http->get('/v2/fii/indicators', ['symbols' => $symbol]);
                if ($response->status() === 403) {
                    return null;
                }
                if ($response->failed()) {
                    return null;
                }
                $fiis = $response->json('fiis');

                return $fiis[0] ?? null;
            } catch (\Throwable $e) {
                Log::warning("brapi fii indicators error {$symbol}: {$e->getMessage()}");

                return null;
            }
        });
    }

    private function detectAssetType(string $symbol): string
    {
        $suffix = (string) preg_replace('/^[A-Z0-9]{4}/', '', strtoupper($symbol));

        if ($suffix === '11') {
            return 'fii';
        }
        if (preg_match('/^3[1-9]$/', $suffix)) {
            return 'bdr';
        }
        if (preg_match('/^[0-9]B$/i', $suffix)) {
            return 'invalid';
        }
        if (preg_match('/^[3-8]$/', $suffix)) {
            return 'stock';
        }

        return 'invalid';
    }

    public function fetchAvailableTickers(int $cacheMinutes = 1440): array
    {
        $cacheKey = 'brapi_available';

        return Cache::remember($cacheKey, $cacheMinutes, function () {
            try {
                $response = $this->http->get('/available');

                if ($response->failed()) {
                    Log::warning('brapi.dev available API error', [
                        'status' => $response->status(),
                    ]);

                    return [];
                }

                return $response->json('stocks') ?? [];
            } catch (\Throwable $e) {
                Log::error('brapi.dev available error: '.$e->getMessage());

                return [];
            }
        });
    }

    public function fetchHistoricalPrices(string $symbol, string $range = '1y', string $interval = '1mo'): Collection
    {
        $symbol = strtoupper(trim($symbol));
        $cacheKey = 'brapi_historical_prices_'.$symbol.'_'.$range.'_'.$interval;

        return Cache::remember($cacheKey, 360, function () use ($symbol, $range, $interval) {
            try {
                $response = $this->http->get('/v2/stocks/quote', [
                    'symbols' => $symbol,
                    'range' => $range,
                    'interval' => $interval,
                    'historicalPrice' => 'true',
                ]);

                if ($response->failed()) {
                    Log::warning('brapi.dev historical prices API error', [
                        'symbol' => $symbol,
                        'status' => $response->status(),
                    ]);

                    return collect();
                }

                $results = $response->json('results');
                if (empty($results) || ! isset($results[0]['data'])) {
                    return collect();
                }

                $data = $results[0]['data'];
                $historicalData = $data['historicalDataPrice'] ?? [];

                return collect($historicalData)
                    ->filter(fn (array $item): bool => isset($item['close']) && $item['close'] !== null)
                    ->map(fn (array $item): array => [
                        'date' => $item['date'] ?? null,
                        'close' => (float) $item['close'],
                        'open' => (float) ($item['open'] ?? 0),
                        'high' => (float) ($item['high'] ?? 0),
                        'low' => (float) ($item['low'] ?? 0),
                        'volume' => (int) ($item['volume'] ?? 0),
                    ])
                    ->sortBy('date')
                    ->values();
            } catch (\Throwable $e) {
                Log::error('brapi.dev historical prices error: '.$e->getMessage());

                return collect();
            }
        });
    }

    public function fetchHistoricalDividends(string $symbol): Collection
    {
        $symbol = strtoupper(trim($symbol));
        $cacheKey = 'brapi_dividends_'.$symbol;

        return Cache::remember($cacheKey, 360, function () use ($symbol) {
            try {
                $response = $this->http->get('/v2/stocks/quote', [
                    'symbols' => $symbol,
                    'dividends' => 'true',
                ]);

                if ($response->failed()) {
                    Log::warning('brapi.dev dividends API error', [
                        'symbol' => $symbol,
                        'status' => $response->status(),
                    ]);

                    return collect();
                }

                $results = $response->json('results');
                if (empty($results) || ! isset($results[0]['data'])) {
                    return collect();
                }

                $data = $results[0]['data'];
                $dividendsData = $data['dividendsData'] ?? [];

                return collect($dividendsData)
                    ->sortBy('date')
                    ->map(fn (array $div): array => [
                        'date' => $div['date'] ?? null,
                        'value' => (float) ($div['value'] ?? $div['dividend'] ?? 0),
                        'type' => $div['type'] ?? 'DIVIDEND',
                    ]);
            } catch (\Throwable $e) {
                Log::error('brapi.dev dividends error: '.$e->getMessage());

                return collect();
            }
        });
    }
}
