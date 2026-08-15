<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class StatusInvestScraperService
{
    private const BASE_URL = 'https://statusinvest.com.br';

    public function fetchAllStocks(): array
    {
        return $this->fetchBulkData(1);
    }

    public function fetchAllFii(): array
    {
        return $this->fetchBulkData(2);
    }

    public function fetchStockIndicators(string $ticker): ?array
    {
        $ticker = strtoupper(trim($ticker));

        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(15)
                ->get(self::BASE_URL.'/acao/indicatorresult', ['code' => $ticker]);

            if ($response->failed()) {
                return null;
            }

            $data = $response->json();
            if (empty($data)) {
                return null;
            }

            return $this->normalizeStockIndicators($data);
        } catch (\Throwable $e) {
            Log::warning("StatusInvest stock indicators error {$ticker}: {$e->getMessage()}");

            return null;
        }
    }

    public function fetchFiiIndicators(string $ticker): ?array
    {
        $ticker = strtoupper(trim($ticker));

        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(15)
                ->get(self::BASE_URL.'/fii/indicatorresult', ['code' => $ticker]);

            if ($response->failed()) {
                return null;
            }

            $data = $response->json();
            if (empty($data)) {
                return null;
            }

            return $this->normalizeFiiIndicators($data);
        } catch (\Throwable $e) {
            Log::warning("StatusInvest FII indicators error {$ticker}: {$e->getMessage()}");

            return null;
        }
    }

    private function fetchBulkData(int $categoryType): array
    {
        $cacheKey = "statusinvest_bulk_{$categoryType}";

        return Cache::remember($cacheKey, 3600, function () use ($categoryType): array {
            $allResults = [];
            $seenTickers = [];

            foreach (range(1, 14) as $sectorId) {
                $results = $this->fetchBulkPage($categoryType, (string) $sectorId);

                foreach ($results as $item) {
                    $ticker = $item['ticker'] ?? null;
                    if ($ticker === null || isset($seenTickers[$ticker])) {
                        continue;
                    }
                    $seenTickers[$ticker] = true;
                    $allResults[] = $item;
                }

                usleep(200_000);
            }

            return $allResults;
        });
    }

    private function fetchBulkPage(int $categoryType, string $sector): array
    {
        try {
            $searchPayload = json_encode([
                'Sector' => $sector,
                'SubSector' => '',
                'Segment' => '',
            ]);

            $response = Http::withHeaders($this->headers())
                ->timeout(60)
                ->get(self::BASE_URL.'/category/advancedsearchresult', [
                    'search' => $searchPayload,
                    'CategoryType' => $categoryType,
                ]);

            if ($response->failed()) {
                Log::warning('StatusInvest bulk search failed', [
                    'categoryType' => $categoryType,
                    'sector' => $sector,
                    'status' => $response->status(),
                ]);

                return [];
            }

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::warning("StatusInvest bulk search error (sector {$sector}): {$e->getMessage()}");

            return [];
        }
    }

    private function normalizeStockIndicators(array $data): array
    {
        $get = fn (string $key) => $data[$key] ?? null;

        $currentPrice = $this->toFloat($get('price'));
        $dividendYield = $this->toPercent($get('dy'));

        $ebitda = $this->toFloat($get('ebitda')) ?? $this->toFloat($get('lajida'));
        $netDebt = $this->toFloat($get('dividaLiquida')) ?? $this->toFloat($get('divida_Liquida'));
        $grossDebt = $this->toFloat($get('dividaBruta')) ?? $this->toFloat($get('divida_Bruta'));

        return [
            'current_price' => $currentPrice,
            'dividend_yield' => $dividendYield,
            'price_to_earnings' => $this->toFloat($get('p_L')),
            'price_to_book' => $this->toFloat($get('p_VP')),
            'price_to_assets' => $this->toFloat($get('p_Ativo')),
            'price_to_cash_flow' => $this->toFloat($get('p_Cap_Giro')),
            'ev_to_ebitda' => $this->toFloat($get('eV_Ebit')),
            'price_to_sales' => $this->toFloat($get('p_SR')),
            'roe' => $this->toPercent($get('roe')),
            'roa' => $this->toPercent($get('roa')),
            'profit_margin' => $this->toPercent($get('marg_Liq')),
            'ebitda_margin' => $this->toPercent($get('marg_EBIT')),
            'gross_margin' => $this->toPercent($get('marg_Bruta')),
            'ebitda' => $ebitda,
            'net_debt' => $netDebt,
            'gross_debt' => $grossDebt,
            'net_debt_to_ebitda' => $this->toFloat($get('dividaLiquidaEbit')),
            'current_liquidity' => $this->toFloat($get('liquidez_Corr')),
            'payout' => $this->toPercent($get('payout')),
            'market_cap' => $this->toFloat($get('valor_Mercado')),
            'volume_avg_30d' => $this->toFloat($get('liquidez_Media_Diaria')),
            'dividends_per_share' => $currentPrice !== null && $dividendYield !== null
                ? round($dividendYield / 100 * $currentPrice, 4)
                : null,
            'total_shares' => $this->toInt($get('nro_Acoes')),
            'free_cash_flow' => $this->toFloat($get('fcf_Liq')),
        ];
    }

    private function normalizeFiiIndicators(array $data): array
    {
        $get = fn (string $key) => $data[$key] ?? null;

        return [
            'current_price' => $this->toFloat($get('price')),
            'dividend_yield' => $this->toPercent($get('dy')),
            'p_vp' => $this->toFloat($get('p_VP')),
            'price_to_book' => $this->toFloat($get('p_VP')),
            'cap_rate' => $this->toPercent($get('capRate')),
            'vacancy_rate' => $this->toPercent($get('vacancy')),
            'vacancy_financial' => $this->toPercent($get('vacancyFinancial')) ?? $this->toFloat($get('vacancyFinancial')),
            'average_maturity' => $this->toFloat($get('averageMaturity')),
            'number_of_properties' => $this->toInt($get('numberProperties')),
            'rental_area' => $this->toFloat($get('rentalArea')),
            'ffo_yield' => $this->toPercent($get('ffoYield')),
            'net_worth' => $this->toFloat($get('netWorth')),
            'book_value_per_share' => $this->toFloat($get('navPerShare')),
            'market_cap' => $this->toFloat($get('marketCap')),
            'volume_avg_30d' => $this->toFloat($get('averageDailyLiquidity')),
            'dividends_per_share' => $this->toFloat($get('dividend')),
            'total_shares' => $this->toInt($get('sharesOutstanding')),
        ];
    }

    private function toFloat(mixed $value): ?float
    {
        if ($value === null || $value === '' || $value === '-' || $value === '--' || $value === 'N/A') {
            return null;
        }
        if (is_bool($value) || is_array($value) || is_object($value)) {
            return null;
        }
        if (is_numeric($value)) {
            return (float) $value;
        }
        $clean = preg_replace('/[\s\xA0\x{00A0}]+/u', '', (string) $value);
        $clean = str_replace(['R$', '$', '%'], '', $clean);
        $clean = str_replace(['.', ','], ['', '.'], $clean);
        if (is_numeric($clean)) {
            return (float) $clean;
        }

        return null;
    }

    private function toPercent(mixed $value): ?float
    {
        $float = $this->toFloat($value);
        if ($float === null) {
            return null;
        }

        return round($float, 4);
    }

    private function toInt(mixed $value): ?int
    {
        if ($value === null || $value === '' || $value === '-') {
            return null;
        }

        return (int) $value;
    }

    private function headers(): array
    {
        return [
            'Accept' => 'application/json, text/plain, */*',
            'Accept-Language' => 'pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7',
            'Referer' => self::BASE_URL,
            'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36',
            'X-Requested-With' => 'XMLHttpRequest',
        ];
    }
}
