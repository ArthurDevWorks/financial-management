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
        private readonly FundamentusScraperService $fundamentus,
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

        if ($type === 'all') {
            $result = $this->syncMissingFromBrapi($onProgress);
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
                // Coleta de todas as fontes disponíveis e mescla campo a campo
                // para garantir máxima cobertura de indicadores
                $sources = [];

                $si = $this->statusInvest->fetchStockIndicators($ticker);
                if ($si) {
                    $sources[] = $si;
                }

                $fund = $this->fundamentus->fetchStockIndicators($ticker);
                if ($fund) {
                    $sources[] = $fund;
                }

                $inv10 = $this->investidor10->fetchStockIndicators($ticker);
                if ($inv10) {
                    $sources[] = $inv10;
                }

                // Mescla: cada fonte só preenche campos ainda ausentes
                foreach ($sources as $source) {
                    foreach ($source as $key => $value) {
                        if ($value !== null && ($data[$key] ?? null) === null) {
                            $data[$key] = $value;
                        }
                    }
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
                    'book_value_per_share', 'total_shares', 'ebitda', 'net_debt', 'gross_debt',
                ], fn ($key) => isset($quote[$key]) && $quote[$key] !== null);

                foreach ($stockIndicators as $key) {
                    $data[$key] ??= $quote[$key];
                }

                if (empty($data['ebitda']) && ! empty($data['enterprise_value']) && ! empty($data['ev_to_ebitda']) && $data['ev_to_ebitda'] > 0) {
                    $data['ebitda'] = round($data['enterprise_value'] / $data['ev_to_ebitda'], 2);
                }

                if (empty($data['net_debt']) && ! empty($data['ebitda']) && isset($data['net_debt_to_ebitda']) && $data['net_debt_to_ebitda'] !== null) {
                    $data['net_debt'] = round($data['ebitda'] * $data['net_debt_to_ebitda'], 2);
                } elseif (empty($data['net_debt']) && ! empty($data['enterprise_value']) && ! empty($data['market_cap'])) {
                    $data['net_debt'] = round($data['enterprise_value'] - $data['market_cap'], 2);
                }

                if (empty($data['total_shares']) && ! empty($data['market_cap']) && ! empty($data['current_price']) && $data['current_price'] > 0) {
                    $data['total_shares'] = (int) round($data['market_cap'] / $data['current_price']);
                }

                if (! empty($data['total_shares']) && ! empty($data['current_price']) && $data['current_price'] > 0) {
                    $expectedCap = round($data['total_shares'] * $data['current_price'], 2);
                    if (empty($data['market_cap']) || $data['market_cap'] < ($expectedCap * 0.2)) {
                        $data['market_cap'] = $expectedCap;
                    }
                }
            }

            if (empty($data)) {
                return false;
            }

            $data['ticker'] = $ticker;
            $data['asset_type'] = $assetType;
            $data['fetched_at'] = now();

            // Normalização de setor, nome e segmento
            $data = $this->applyNormalizers($data, $ticker, $assetType);

            $data = $this->sanitizeAssetData($data);

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

            if ($ticker === null || ! $this->isStockOrFii($ticker, 'stock')) {
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

            if ($ticker === null || ! $this->isStockOrFii($ticker, 'fii')) {
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
                    'ebitda' => $quote['ebitda'] ?? null,
                    'net_debt' => $quote['net_debt'] ?? null,
                    'gross_debt' => $quote['gross_debt'] ?? null,
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

                // Normalização de setor, nome e segmento
                $fieldsToUpdate = $this->applyNormalizers($fieldsToUpdate, $ticker, $existing->asset_type);

                $fieldsToUpdate = $this->sanitizeAssetData($fieldsToUpdate);

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

        if (! $force && $existing && $existing->fetched_at && $existing->fetched_at->diffInHours(now()) < $maxHoursSinceUpdate) {
            return false;
        }

        $data = $this->mapBulkItem($item, $assetType);

        $data['ticker'] = $ticker;
        $data['asset_type'] = $assetType;
        $data['fetched_at'] = now();

        // Normalização de setor, nome e segmento
        $data = $this->applyNormalizers($data, $ticker, $assetType);

        $data = $this->sanitizeAssetData($data);

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

        $currentPrice = $this->toFloat($get('price'));
        $dividendYield = $this->toPercent($get('dy'));

        $ebitda = $this->toFloat($get('ebitda')) ?? $this->toFloat($get('lajida'));
        $netDebt = $this->toFloat($get('dividaliquida')) ?? $this->toFloat($get('divida_liquida'));
        $grossDebt = $this->toFloat($get('dividabruta')) ?? $this->toFloat($get('divida_bruta'));

        $evEbitda = $this->toFloat($get('ev_ebit'));
        $marketCap = $this->toFloat($get('valormercado'));
        $netDebtToEbitda = $this->toFloat($get('dividaliquidaebit'));

        if ($ebitda === null && $marketCap !== null && $evEbitda !== null && $evEbitda > 0) {
            $ev = $marketCap + ($netDebt ?? 0);
            $ebitda = round($ev / $evEbitda, 2);
        }

        if ($netDebt === null && $ebitda !== null && $netDebtToEbitda !== null) {
            $netDebt = round($ebitda * $netDebtToEbitda, 2);
        }

        $data = [
            'name' => $get('companyname') ?? $get('name'),
            'current_price' => $currentPrice,
            'dividend_yield' => $dividendYield,
            'price_to_earnings' => $this->toFloat($get('p_l')),
            'price_to_book' => $this->toFloat($get('p_vp')),
            'price_to_assets' => $this->toFloat($get('p_ativo')),
            'price_to_cash_flow' => $this->toFloat($get('p_capitalgiro')),
            'ev_to_ebitda' => $evEbitda,
            'price_to_sales' => $this->toFloat($get('p_sr')),
            'roe' => $this->toPercent($get('roe')),
            'roa' => $this->toPercent($get('roa')),
            'profit_margin' => $this->toPercent($get('margemliquida')),
            'ebitda_margin' => $this->toPercent($get('margemebit')),
            'gross_margin' => $this->toPercent($get('margembruta')),
            'ebitda' => $ebitda,
            'net_debt' => $netDebt,
            'gross_debt' => $grossDebt,
            'net_debt_to_ebitda' => $netDebtToEbitda,
            'current_liquidity' => $this->toFloat($get('liquidezcorrente')),
            'payout' => $this->toPercent($get('payout')),
            'market_cap' => $this->toFloat($get('valormercado')),
            'volume_avg_30d' => $this->toFloat($get('liquidezmediadiaria')),
            'dividends_per_share' => $currentPrice !== null && $dividendYield !== null
                ? round($dividendYield / 100 * $currentPrice, 4)
                : null,
            'total_shares' => $this->computeTotalSharesFromMarketCap(
                $this->toFloat($get('valormercado')),
                $this->toFloat($get('price')),
            ),
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
            $data['total_shares'] = $this->toInt($get('numerocotas'));
        }

        return $data;
    }

    public function syncMissingFromBrapi(?callable $onProgress = null): array
    {
        $available = $this->brapi->fetchAvailableTickers();

        if (empty($available)) {
            return ['processed' => 0, 'updated' => 0, 'errors' => 0, 'missing' => 0];
        }

        $existingTickers = Asset::pluck('ticker')->map(fn ($t) => strtoupper(trim($t)))->flip();

        $missing = collect($available)
            ->map(fn ($t) => strtoupper(trim($t)))
            ->unique()
            ->reject(fn ($t) => (bool) preg_match('/F$/', $t))
            ->filter(fn ($t) => ! $existingTickers->has($t))
            ->filter(fn ($t) => $this->detectAssetType($t) === 'stock' || $this->detectAssetType($t) === 'fii')
            ->values()
            ->all();

        if (empty($missing)) {
            return ['processed' => 0, 'updated' => 0, 'errors' => 0, 'missing' => 0];
        }

        $processed = 0;
        $updated = 0;
        $errors = 0;
        $total = count($missing);

        foreach ($missing as $i => $ticker) {
            $processed++;

            if ($onProgress) {
                $onProgress($ticker, $i + 1, $total);
            }

            try {
                if ($this->syncSingleFromBrapi($ticker)) {
                    $updated++;
                }
            } catch (\Throwable $e) {
                $errors++;
                Log::warning("Error syncing missing asset {$ticker}: {$e->getMessage()}");
            }

            usleep(200_000);
        }

        return compact('processed', 'updated', 'errors', 'missing');
    }

    public function syncSingleFromBrapi(string $ticker): bool
    {
        $ticker = strtoupper(trim($ticker));
        $assetType = $this->detectAssetType($ticker);

        if ($assetType === 'invalid') {
            return false;
        }

        if ($assetType === 'fii') {
            $fiiData = $this->brapi->fetchFiiIndicators($ticker);
            if ($fiiData === null) {
                return false;
            }
        }

        try {
            $quote = $this->brapi->fetchCompleteQuote($ticker);
            if ($quote === null) {
                return false;
            }

            $data = [
                'ticker' => $ticker,
                'asset_type' => $assetType,
                'name' => $quote['name'] ?? $ticker,
                'current_price' => $quote['current_price'] ?? null,
                'logo_url' => $quote['logourl'] ?? null,
                'sector' => $quote['sector'] ?? null,
                'subsector' => $quote['industry'] ?? null,
                'market_cap' => $quote['market_cap'] ?? null,
                'enterprise_value' => $quote['enterprise_value'] ?? null,
                'volume_avg_30d' => $quote['volume_avg_30d'] ?? null,
                'dividend_yield' => $quote['dividend_yield'] ?? null,
                'price_to_earnings' => $quote['price_to_earnings'] ?? null,
                'price_to_book' => $quote['price_to_book'] ?? null,
                'ev_to_ebitda' => $quote['ev_to_ebitda'] ?? null,
                'price_to_sales' => $quote['price_to_sales'] ?? null,
                'price_to_assets' => $quote['price_to_assets'] ?? null,
                'price_to_cash_flow' => $quote['price_to_cash_flow'] ?? null,
                'roe' => $quote['roe'] ?? null,
                'roa' => $quote['roa'] ?? null,
                'profit_margin' => $quote['profit_margin'] ?? null,
                'ebitda_margin' => $quote['ebitda_margin'] ?? null,
                'gross_margin' => $quote['gross_margin'] ?? null,
                'ebitda' => $quote['ebitda'] ?? null,
                'net_debt' => $quote['net_debt'] ?? null,
                'gross_debt' => $quote['gross_debt'] ?? null,
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
                'long_business_summary' => $quote['long_business_summary'] ?? null,
                'website' => $quote['website'] ?? null,
                'full_time_employees' => $quote['full_time_employees'] ?? null,
                'fetched_at' => now(),
            ];

            if (empty($data['total_shares']) && ! empty($data['market_cap']) && ! empty($data['current_price'])) {
                $data['total_shares'] = (int) round($data['market_cap'] / $data['current_price']);
            }

            $clean = array_filter($data, fn ($v) => $v !== null);

            // Normalização de setor, nome e segmento
            $clean = $this->applyNormalizers($clean, $ticker, $assetType);

            $clean = $this->sanitizeAssetData($clean);

            if (empty($clean)) {
                return false;
            }

            Asset::updateOrCreate(['ticker' => $ticker], $clean);

            return true;
        } catch (\Throwable $e) {
            Log::warning("Error syncing asset from Brapi {$ticker}: {$e->getMessage()}");

            return false;
        }
    }

    private function isStockOrFii(string $ticker, string $expectedType): bool
    {
        $detected = $this->detectAssetType($ticker);

        if ($expectedType === 'fii') {
            return $detected === 'fii';
        }

        return $detected === 'stock';
    }

    /**
     * Complementa campos ausentes buscando nas fontes disponíveis.
     * Garante que nenhum campo importante fique null se alguma fonte tiver o dado.
     */
    private function fillMissingFields(array &$data, string $ticker): void
    {
        $needsInvestidor10 = $this->hasMissingCriticalFields($data);

        if ($needsInvestidor10) {
            $inv10 = $this->investidor10->fetchStockIndicators($ticker);
            if ($inv10) {
                foreach ($inv10 as $key => $value) {
                    if (($data[$key] ?? null) === null && $value !== null) {
                        $data[$key] = $value;
                    }
                }
            }
        }

        $needsFundamentus = $this->hasMissingCriticalFields($data);

        if ($needsFundamentus) {
            $fund = $this->fundamentus->fetchStockIndicators($ticker);
            if ($fund) {
                foreach ($fund as $key => $value) {
                    if (($data[$key] ?? null) === null && $value !== null) {
                        $data[$key] = $value;
                    }
                }
            }
        }
    }

    private function hasMissingCriticalFields(array $data): bool
    {
        $critical = ['dividend_yield', 'price_to_earnings', 'price_to_book', 'roe', 'profit_margin'];

        foreach ($critical as $field) {
            if (($data[$field] ?? null) === null) {
                return true;
            }
        }

        return false;
    }

    private function detectAssetType(string $ticker): string
    {
        $suffix = (string) preg_replace('/^[A-Z0-9]{4}/', '', strtoupper($ticker));

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

    private function computeTotalSharesFromMarketCap(?float $marketCap, ?float $price): ?int
    {
        if ($marketCap !== null && $price !== null && $price > 0) {
            return (int) round($marketCap / $price);
        }

        return null;
    }

    private function toInt(mixed $value): ?int
    {
        $float = $this->toFloat($value);
        if ($float === null) {
            return null;
        }

        return (int) round($float);
    }

    private function sanitizeAssetData(array $data): array
    {
        $decimalFields = [
            'current_price', 'market_cap', 'enterprise_value', 'volume_avg_30d',
            'dividend_yield', 'price_to_earnings', 'price_to_book', 'ev_to_ebitda',
            'price_to_sales', 'price_to_assets', 'price_to_cash_flow',
            'roe', 'roa', 'profit_margin', 'ebitda_margin', 'gross_margin',
            'ebitda', 'net_debt', 'gross_debt', 'debt_to_ebitda', 'net_debt_to_ebitda',
            'current_liquidity', 'payout', 'net_income', 'revenue', 'free_cash_flow',
            'dividends_per_share', 'earnings_per_share', 'book_value_per_share',
            'p_vp', 'cap_rate', 'vacancy_rate', 'vacancy_financial',
            'average_maturity', 'rental_area', 'ffo_yield', 'net_worth',
        ];

        $intFields = [
            'total_shares', 'number_of_properties', 'full_time_employees',
        ];

        foreach ($decimalFields as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = $this->toFloat($data[$field]);
            }
        }

        foreach ($intFields as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = $this->toInt($data[$field]);
            }
        }

        return $data;
    }

    /**
     * Aplica normalizadores de setor, nome e segmento ao array de dados.
     */
    private function applyNormalizers(array $data, string $ticker, string $assetType): array
    {
        $sector = $data['sector'] ?? null;
        $subsector = $data['subsector'] ?? null;
        $segment = $data['segment'] ?? null;
        $name = $data['name'] ?? null;

        if ($assetType === 'fii') {
            $fii = FiiSegmentMapper::normalize($segment ?? $subsector, $subsector, $assetType);
            if ($fii['segment'] !== null) {
                $data['segment'] = $fii['segment'];
            }
            if ($fii['subsector'] !== null) {
                $data['subsector'] = $fii['subsector'];
            }
        } else {
            $mapped = SectorMapper::normalize($sector, $subsector);
            if ($mapped['sector'] !== null) {
                $data['sector'] = $mapped['sector'];
            }
            if ($mapped['subsector'] !== null) {
                $data['subsector'] = $mapped['subsector'];
            }
        }

        $normalizedName = NameNormalizer::normalize($ticker, $name);
        if ($normalizedName !== null) {
            $data['name'] = $normalizedName;
        }

        return $data;
    }
}
