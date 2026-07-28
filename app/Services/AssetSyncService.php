<?php

namespace App\Services;

use App\Models\Asset;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssetSyncService
{
    public function __construct(
        private readonly BrapiService $brapi,
        private readonly StatusInvestScraperService $statusInvest,
        private readonly Investidor10ScraperService $investidor10,
    ) {}

    public function sync(
        string $type = 'all',
        bool $force = false,
        int $maxHoursSinceUpdate = 4,
        ?callable $onProgress = null,
    ): array {
        $processed = 0;
        $updated = 0;
        $errors = 0;
        $enrichedTickers = [];

        if ($type === 'all' || $type === 'stock') {
            $result = $this->syncBulkStocks($force, $maxHoursSinceUpdate, $onProgress);
            $processed += $result['processed'];
            $updated += $result['updated'];
            $errors += $result['errors'];
            $enrichedTickers = array_merge($enrichedTickers, $result['syncedTickers'] ?? []);
        }

        if ($type === 'all' || $type === 'fii') {
            $result = $this->syncBulkFii($force, $maxHoursSinceUpdate, $onProgress);
            $processed += $result['processed'];
            $updated += $result['updated'];
            $errors += $result['errors'];
            $enrichedTickers = array_merge($enrichedTickers, $result['syncedTickers'] ?? []);
        }

        if (! empty($enrichedTickers)) {
            $result = $this->enrichFromBrapi($enrichedTickers, $onProgress);
            $processed += $result['processed'];
            $updated += $result['updated'];
            $errors += $result['errors'];
        }

        $this->enrichLogos([]);

        return [
            'processed' => $processed,
            'updated' => $updated,
            'errors' => $errors,
        ];
    }

    public function syncSingle(string $ticker, bool $force = false): bool
    {
        $ticker = strtoupper(trim($ticker));
        $assetType = $this->detectAssetType($ticker);

        try {
            $data = [];

            if ($assetType === 'fii') {
                $indicators = $this->statusInvest->fetchFiiIndicators($ticker);
                if ($indicators === null) {
                    $indicators = $this->investidor10->fetchFiiIndicators($ticker);
                }
                if ($indicators) {
                    $data = array_merge($data, $indicators);
                }
            } else {
                $indicators = $this->statusInvest->fetchStockIndicators($ticker);
                if ($indicators === null) {
                    $indicators = $this->investidor10->fetchStockIndicators($ticker);
                }
                if ($indicators) {
                    $data = array_merge($data, $indicators);
                }
            }

            $quote = $this->brapi->fetchCompleteQuote($ticker);
            if ($quote) {
                $data['current_price'] = $quote['current_price'] ?? $data['current_price'] ?? null;
                $data['name'] = $quote['name'] ?? $ticker;
                $data['logo_url'] = $quote['logourl'] ?? null;
                $data['sector'] = $quote['sector'] ?? $data['sector'] ?? null;
                $data['subsector'] = $quote['industry'] ?? $data['subsector'] ?? null;

                $stockIndicators = array_filter([
                    'market_cap', 'enterprise_value', 'volume_avg_30d',
                    'dividend_yield', 'price_to_earnings', 'price_to_book',
                    'ev_to_ebitda', 'roe', 'net_income', 'revenue',
                    'free_cash_flow', 'dividends_per_share', 'earnings_per_share',
                    'book_value_per_share', 'total_shares',
                ], fn ($key) => isset($quote[$key]) && $quote[$key] !== null);

                foreach ($stockIndicators as $key) {
                    $data[$key] ??= $quote[$key];
                }
            }

            if (empty($data)) {
                return false;
            }

            $data['ticker'] = $ticker;
            $data['asset_type'] = $assetType;
            $data['fetched_at'] = now();

            Asset::updateOrCreate(['ticker' => $ticker], $data);

            return true;
        } catch (\Throwable $e) {
            Log::warning("Error syncing single asset {$ticker}: {$e->getMessage()}");
            return false;
        }
    }

    private function syncBulkStocks(
        bool $force,
        int $maxHoursSinceUpdate,
        ?callable $onProgress,
    ): array {
        $tickers = $this->statusInvest->fetchAllStocks();

        if (empty($tickers)) {
            return ['processed' => 0, 'updated' => 0, 'errors' => 0, 'syncedTickers' => []];
        }

        $processed = 0;
        $updated = 0;
        $errors = 0;
        $total = count($tickers);
        $syncedTickers = [];

        foreach ($tickers as $i => $item) {
            $ticker = is_array($item) ? ($item['ticker'] ?? $item['code'] ?? null) : null;

            if ($ticker === null || !$this->isStockOrFii($ticker, 'stock')) {
                continue;
            }

            $processed++;

            if ($onProgress) {
                $onProgress($ticker, $i + 1, $total);
            }

            try {
                if ($this->updateAssetFromBulkItem($ticker, $item, 'stock', $force, $maxHoursSinceUpdate)) {
                    $updated++;
                    $syncedTickers[] = $ticker;
                }
            } catch (\Throwable $e) {
                $errors++;
                Log::warning("Error syncing stock {$ticker}: {$e->getMessage()}");
            }

            if ($i < $total - 1) {
                usleep(200_000);
            }
        }

        return compact('processed', 'updated', 'errors', 'syncedTickers');
    }

    private function syncBulkFii(
        bool $force,
        int $maxHoursSinceUpdate,
        ?callable $onProgress,
    ): array {
        $tickers = $this->statusInvest->fetchAllFii();

        if (empty($tickers)) {
            return ['processed' => 0, 'updated' => 0, 'errors' => 0, 'syncedTickers' => []];
        }

        $processed = 0;
        $updated = 0;
        $errors = 0;
        $total = count($tickers);
        $syncedTickers = [];

        foreach ($tickers as $i => $item) {
            $ticker = is_array($item) ? ($item['ticker'] ?? $item['code'] ?? null) : null;

            if ($ticker === null || !$this->isStockOrFii($ticker, 'fii')) {
                continue;
            }

            $processed++;

            if ($onProgress) {
                $onProgress($ticker, $i + 1, $total);
            }

            try {
                if ($this->updateAssetFromBulkItem($ticker, $item, 'fii', $force, $maxHoursSinceUpdate)) {
                    $updated++;
                    $syncedTickers[] = $ticker;
                }
            } catch (\Throwable $e) {
                $errors++;
                Log::warning("Error syncing FII {$ticker}: {$e->getMessage()}");
            }

            if ($i < $total - 1) {
                usleep(200_000);
            }
        }

        return compact('processed', 'updated', 'errors', 'syncedTickers');
    }

    private function enrichLogos(array $tickers): void
    {
        Asset::whereNull('logo_url')->orWhere('logo_url', '')
            ->update([
                'logo_url' => DB::raw("CONCAT('https://icons.brapi.dev/icons/', ticker, '.svg')"),
            ]);
    }

    public function enrichFromBrapi(array $tickers, ?callable $onProgress = null): array
    {
        $processed = 0;
        $updated = 0;
        $errors = 0;
        $total = count($tickers);

        foreach ($tickers as $i => $ticker) {
            $processed++;

            if ($onProgress) {
                $onProgress($ticker, $i + 1, $total);
            }

            try {
                $quote = $this->brapi->fetchCompleteQuote($ticker);
                if ($quote === null) {
                    continue;
                }

                $existing = Asset::where('ticker', $ticker)->first();
                if ($existing === null) {
                    continue;
                }

                $fieldsToUpdate = array_filter([
                    'name' => $quote['name'] ?? null,
                    'current_price' => $quote['current_price'] ?? null,
                    'logo_url' => $quote['logourl'] ?? null,
                    'market_cap' => $quote['market_cap'] ?? null,
                    'enterprise_value' => $quote['enterprise_value'] ?? null,
                    'volume_avg_30d' => $quote['volume_avg_30d'] ?? null,
                    'dividend_yield' => $quote['dividend_yield'] ?? null,
                    'price_to_earnings' => $quote['price_to_earnings'] ?? null,
                    'price_to_book' => $quote['price_to_book'] ?? null,
                    'ev_to_ebitda' => $quote['ev_to_ebitda'] ?? null,
                    'price_to_sales' => $quote['price_to_sales'] ?? null,
                    'roe' => $quote['roe'] ?? null,
                    'roa' => $quote['roa'] ?? null,
                    'profit_margin' => $quote['profit_margin'] ?? null,
                    'ebitda_margin' => $quote['ebitda_margin'] ?? null,
                    'gross_margin' => $quote['gross_margin'] ?? null,
                    'debt_to_ebitda' => $quote['debt_to_ebitda'] ?? null,
                    'net_debt_to_ebitda' => $quote['net_debt_to_ebitda'] ?? null,
                    'current_liquidity' => $quote['current_liquidity'] ?? null,
                    'payout' => $quote['payout'] ?? null,
                    'net_income' => $quote['net_income'] ?? null,
                    'revenue' => $quote['revenue'] ?? null,
                    'free_cash_flow' => $quote['free_cash_flow'] ?? null,
                    'dividends_per_share' => $quote['dividends_per_share'] ?? null,
                    'earnings_per_share' => $quote['earnings_per_share'] ?? null,
                    'book_value_per_share' => $quote['book_value_per_share'] ?? null,
                    'total_shares' => $quote['total_shares'] ?? null,
                    'sector' => $quote['sector'] ?? null,
                    'subsector' => $quote['industry'] ?? null,
                    'long_business_summary' => $quote['long_business_summary'] ?? null,
                    'website' => $quote['website'] ?? null,
                    'full_time_employees' => $quote['full_time_employees'] ?? null,
                ], fn ($v) => $v !== null);

                if (! empty($fieldsToUpdate)) {
                    $existing->update($fieldsToUpdate);
                    $updated++;
                }
            } catch (\Throwable $e) {
                $errors++;
                Log::warning("Error enriching {$ticker} from Brapi: {$e->getMessage()}");
            }

            usleep(200_000);
        }

        return compact('processed', 'updated', 'errors');
    }

    private function updateAssetFromBulkItem(
        string $ticker,
        array $item,
        string $assetType,
        bool $force,
        int $maxHoursSinceUpdate,
    ): bool {
        $existing = Asset::where('ticker', $ticker)->first();

        if (!$force && $existing && $existing->fetched_at && $existing->fetched_at->diffInHours(now()) < $maxHoursSinceUpdate) {
            return false;
        }

        $data = $this->mapBulkItem($item, $assetType);

        $data['ticker'] = $ticker;
        $data['asset_type'] = $assetType;
        $data['fetched_at'] = now();

        if ($existing) {
            $existing->update($data);
        } else {
            Asset::create($data);
        }

        return true;
    }

    private function mapBulkItem(array $item, string $assetType): array
    {
        $get = fn (string $key) => $item[$key] ?? null;

        $data = [
            'name' => $get('companyname') ?? $get('name'),
            'current_price' => $this->toFloat($get('price')),
            'dividend_yield' => $this->toPercent($get('dy')),
            'price_to_earnings' => $this->toFloat($get('p_l')),
            'price_to_book' => $this->toFloat($get('p_vp')),
            'price_to_assets' => $this->toFloat($get('p_ativo')),
            'price_to_cash_flow' => $this->toFloat($get('p_capitalgiro')),
            'ev_to_ebitda' => $this->toFloat($get('ev_ebit')),
            'price_to_sales' => $this->toFloat($get('p_sr')),
            'roe' => $this->toPercent($get('roe')),
            'roa' => $this->toPercent($get('roa')),
            'profit_margin' => $this->toPercent($get('margemliquida')),
            'ebitda_margin' => $this->toPercent($get('margemebit')),
            'gross_margin' => $this->toPercent($get('margembruta')),
            'debt_to_ebitda' => $this->toFloat($get('dividaliquidaebit')),
            'current_liquidity' => $this->toFloat($get('liquidezcorrente')),
            'payout' => $this->toPercent($get('payout')),
            'market_cap' => $this->toFloat($get('valormercado')),
            'volume_avg_30d' => $this->toFloat($get('liquidezmediadiaria')),
            'dividends_per_share' => $this->toFloat($get('div_Yield')),
            'total_shares' => $this->toInt($get('nro_Acoes')),
            'free_cash_flow' => $this->toFloat($get('fcf_Liq')),
            'sector' => $get('sectorname') ?? $get('sector'),
            'subsector' => $get('subsectorname') ?? $get('subsector'),
            'segment' => $get('segmentname') ?? $get('segment'),
        ];

        if ($assetType === 'fii') {
            $data['p_vp'] = $this->toFloat($get('p_vp'));
            $data['cap_rate'] = $this->toPercent($get('caprate'));
            $data['vacancy_rate'] = $this->toPercent($get('vacancy'));
            $data['number_of_properties'] = $this->toInt($get('numberproperties'));
            $data['net_worth'] = $this->toFloat($get('networth'));
            $data['ffo_yield'] = $this->toPercent($get('ffoyield'));
        }

        return $data;
    }

    private function isStockOrFii(string $ticker, string $expectedType): bool
    {
        $detected = $this->detectAssetType($ticker);

        if ($expectedType === 'fii') {
            return $detected === 'fii';
        }

        return $detected === 'stock';
    }

    private function detectAssetType(string $ticker): string
    {
        if (preg_match('/11$/', $ticker)) {
            return 'fii';
        }
        if (preg_match('/3[45]$/', $ticker)) {
            return 'bdr';
        }
        if (preg_match('/[0-9]$/', $ticker)) {
            return 'stock';
        }
        return 'stock';
    }

    private function toFloat(mixed $value): ?float
    {
        if ($value === null || $value === '' || $value === '-') {
            return null;
        }
        if (is_numeric($value)) {
            return (float) $value;
        }
        $clean = str_replace(['.', ','], ['', '.'], (string) $value);
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
}
