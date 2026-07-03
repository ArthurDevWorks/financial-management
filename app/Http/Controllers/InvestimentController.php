<?php

namespace App\Http\Controllers;

use App\Enums\InvestmentAssetType;
use App\Http\Requests\InvestimentStoreRequest;
use App\Http\Requests\InvestimentUpdateRequest;
use App\Http\Requests\InvestimentValuationRequest;
use App\Models\Investiment;
use App\Models\InvestimentValuation;
use App\Services\BrapiService;
use App\Services\DcfValuationService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;

class InvestimentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, BrapiService $brapi)
    {
        $investiments = Investiment::query()
            ->when($request->search, fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('id', 'desc')
            ->paginate(10);

        $this->syncBrapiQuotes($investiments->getCollection(), $brapi);

        return Inertia::render('investiments/Index', [
            'investiments' => $investiments->through(fn (Investiment $investiment): array => $this->investmentPayload($investiment)),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('investiments/Create', $this->formOptions());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(InvestimentStoreRequest $request, BrapiService $brapi)
    {
        $investiment = Investiment::create($request->validated());

        if (! $investiment->type?->isFixedIncome()) {
            $this->syncBrapiQuotes(collect([$investiment]), $brapi);
        }

        return redirect()->route('investiments.index')
            ->with('success', 'Investimento cadastrado com sucesso');
    }

    /**
     * Display the specified resource.
     */
    public function show(Investiment $investiment, BrapiService $brapi)
    {
        if (! $investiment->type?->isFixedIncome()) {
            $this->syncBrapiQuotes(collect([$investiment]), $brapi);
        }

        $valuationId = request()->query('valuation_id');

        if ($valuationId) {
            /** @var InvestimentValuation|null $specificValuation */
            $specificValuation = $investiment->valuations()
                ->where('id', $valuationId)
                ->where('method', InvestimentValuation::METHOD_DCF)
                ->first();

            return Inertia::render('investiments/Valuation', [
                'investiment' => $this->investmentPayload($investiment),
                'valuation' => $specificValuation ? [
                    'assumptions' => $specificValuation->assumptions,
                    'projected_cash_flows' => $specificValuation->projected_cash_flows,
                    'summary' => $specificValuation->summary,
                    'id' => $specificValuation->id,
                    'method' => $specificValuation->method,
                ] : null,
                'editingValuationId' => $specificValuation?->id,
                'defaultAssumptions' => $this->buildDefaultAssumptions(
                    $investiment,
                    $specificValuation,
                ),
            ]);
        }

        /** @var InvestimentValuation|null $lastValuation */
        $lastValuation = $investiment->valuations()
            ->where('method', InvestimentValuation::METHOD_DCF)
            ->latest('calculated_at')
            ->first();

        return Inertia::render('investiments/Valuation', [
            'investiment' => $this->investmentPayload($investiment),
            'valuation' => $lastValuation ? [
                'assumptions' => $lastValuation->assumptions,
                'projected_cash_flows' => $lastValuation->projected_cash_flows,
                'summary' => $lastValuation->summary,
                'id' => $lastValuation->id,
                'method' => $lastValuation->method,
            ] : null,
            'editingValuationId' => null,
            'defaultAssumptions' => $this->buildDefaultAssumptions($investiment, $lastValuation),
        ]);
    }

    public function valuation(
        InvestimentValuationRequest $request,
        Investiment $investiment,
        DcfValuationService $valuationService,
    ) {
        $validated = $request->validated();
        $calculatedValuation = $valuationService->calculate($validated);

        $valuation = $investiment->valuations()->create([
            'method' => InvestimentValuation::METHOD_DCF,
            'assumptions' => $calculatedValuation['assumptions'],
            'projected_cash_flows' => $calculatedValuation['projected_cash_flows'],
            'summary' => $calculatedValuation['summary'],
            'calculated_at' => now(),
        ]);

        $calculatedValuation['id'] = $valuation->id;
        $calculatedValuation['method'] = $valuation->method;

        return Inertia::render('investiments/Valuation', [
            'investiment' => $this->investmentPayload($investiment),
            'valuation' => $calculatedValuation,
            'editingValuationId' => null,
            'defaultAssumptions' => collect($validated)->map(function (mixed $value): mixed {
                if (is_array($value)) {
                    return array_map(
                        static fn (mixed $item): string => $item === null ? '' : (string) $item,
                        $value,
                    );
                }

                return $value === null ? '' : (string) $value;
            })->all(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Investiment $investiment, BrapiService $brapi)
    {
        if (! $investiment->type?->isFixedIncome()) {
            $this->syncBrapiQuotes(collect([$investiment]), $brapi);
        }

        return Inertia::render('investiments/Edit', [
            'investiment' => $this->investmentPayload($investiment),
            ...$this->formOptions(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(InvestimentUpdateRequest $request, Investiment $investiment, BrapiService $brapi)
    {
        $investiment->update($request->validated());

        if (! $investiment->type?->isFixedIncome()) {
            $this->syncBrapiQuotes(collect([$investiment]), $brapi);
        }

        return redirect()->route('investiments.index')
            ->with('success', 'Investimento atualizado com sucesso');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Investiment $investiment)
    {
        $investiment->delete();

        return redirect()->route('investiments.index')
            ->with('success', 'Investimento removido com sucesso');
    }

    private function formOptions(): array
    {
        return [
            'assetTypes' => array_values(array_filter(
                InvestmentAssetType::options(),
                static fn (array $assetType): bool => $assetType['value'] === InvestmentAssetType::STOCK->value,
            )),
        ];
    }

    private function investmentPayload(Investiment $investiment): array
    {
        $assetType = $investiment->type;
        $profitabilityType = $investiment->profitability_type;
        $indexer = $investiment->indexer;
        $averagePrice = (float) ($investiment->average_price ?? $investiment->value ?? 0);
        $balance = $investiment->balance();
        $profitabilityPercentage = $investiment->profitabilityPercentage();

        return [
            'id' => $investiment->id,
            'name' => $investiment->name,
            'dt_investment' => $investiment->dt_investment?->format('Y-m-d'),
            'type' => $assetType?->value,
            'type_label' => $assetType?->label() ?? 'Não classificado',
            'portfolio_class' => $assetType?->portfolioClass() ?? 'Outros',
            'is_fixed_income' => $assetType?->isFixedIncome() ?? false,
            'quantity' => (float) ($investiment->quantity ?? 0),
            'average_price' => $averagePrice,
            'current_balance' => $balance,
            'current_value' => $balance,
            'initial_value' => $averagePrice,
            'value' => $averagePrice,
            'invested_amount' => $investiment->investedAmount(),
            'gain_loss' => $investiment->gainLoss(),
            'profitability' => $profitabilityPercentage,
            'profitability_percentage' => $profitabilityPercentage,
            'profitability_type' => $profitabilityType?->value,
            'profitability_type_label' => $profitabilityType?->label(),
            'indexer' => $indexer?->value,
            'indexer_label' => $indexer?->label(),
            'contracted_rate' => $investiment->contracted_rate !== null ? (float) $investiment->contracted_rate : null,
            'maturity_date' => $investiment->maturity_date?->format('Y-m-d'),
            'liquidity' => $investiment->liquidity,
            'logo_url' => $investiment->logo_url,
        ];
    }

    private function syncBrapiQuotes(Collection $investiments, BrapiService $brapi): void
    {
        $symbols = $investiments
            ->reject(fn (Investiment $i) => $i->type?->isFixedIncome())
            ->pluck('name')
            ->filter()
            ->map(fn (string $name): string => strtoupper(trim($name)))
            ->values()
            ->all();

        if (empty($symbols)) {
            return;
        }

        $quotes = $brapi->fetchQuotes($symbols);

        if ($quotes->isEmpty()) {
            return;
        }

        $now = now();

        $investiments->each(function (Investiment $investiment) use ($quotes, $now): void {
            $normalized = strtoupper(trim($investiment->name));
            $quote = $quotes->get($normalized);

            if ($quote === null) {
                return;
            }

            $investiment->update([
                'current_balance' => $quote['price'],
                'logo_url' => $quote['logourl'],
                'last_price_fetched_at' => $now,
            ]);
        });
    }

    private function buildDefaultAssumptions(
        Investiment $investiment,
        ?InvestimentValuation $lastValuation,
    ): array {
        $currentPricePerShare = (string) ($investiment->current_balance ?? $investiment->average_price ?? $investiment->value ?? 0);
        $defaultTerminalGrowthRate = '6';

        if ($lastValuation) {
            $assumptions = $lastValuation->assumptions;
            $growthRates = $assumptions['growth_rates'] ?? [];
            $terminalGrowthRate = ! empty($growthRates)
                ? (string) $growthRates[array_key_last($growthRates)]
                : (string) ($assumptions['terminal_growth_rate'] ?? $defaultTerminalGrowthRate);

            return [
                'current_fcf' => (string) ($assumptions['current_fcf'] ?? ''),
                'discount_rate' => (string) ($assumptions['discount_rate'] ?? '12'),
                'terminal_growth_rate' => $terminalGrowthRate,
                'projection_years' => (string) ($assumptions['projection_years'] ?? '5'),
                'total_shares' => (string) ($assumptions['total_shares'] ?? ''),
                'payout' => (string) ($assumptions['payout'] ?? '75'),
                'roe' => (string) ($assumptions['roe'] ?? '24'),
                'current_price_per_share' => (string) ($assumptions['current_price_per_share'] ?? $currentPricePerShare),
                'growth_rates' => $assumptions['growth_rates'] ?? [],
            ];
        }

        return [
            'current_fcf' => '',
            'discount_rate' => '12',
            'terminal_growth_rate' => $defaultTerminalGrowthRate,
            'projection_years' => '5',
            'total_shares' => '',
            'payout' => '75',
            'roe' => '24',
            'current_price_per_share' => $currentPricePerShare,
            'growth_rates' => [],
        ];
    }
}
