<?php

namespace App\Http\Controllers;

use App\Http\Requests\InvestimentValuationRequest;
use App\Models\Investiment;
use App\Models\InvestimentValuation;
use App\Services\BrapiService;
use App\Services\DcfValuationService;
use Inertia\Inertia;

class ValuationController extends Controller
{
    public function index(BrapiService $brapi)
    {
        $investiments = Investiment::query()
            ->whereHas('valuations')
            ->with(['valuations' => fn ($query) => $query->latest('calculated_at')])
            ->orderBy('name')
            ->paginate(15);

        $symbols = $investiments->getCollection()
            ->reject(fn (Investiment $i) => $i->type?->isFixedIncome())
            ->pluck('name')
            ->filter()
            ->map(fn (string $name): string => strtoupper(trim($name)))
            ->values()
            ->all();

        if (! empty($symbols)) {
            $quotes = $brapi->fetchQuotes($symbols);
            $now = now();

            if ($quotes->isNotEmpty()) {
                $investiments->getCollection()->each(function (Investiment $investiment) use ($quotes, $now): void {
                    if ($investiment->type?->isFixedIncome()) {
                        return;
                    }

                    $quote = $quotes->get(strtoupper(trim($investiment->name)));

                    if ($quote === null) {
                        return;
                    }

                    $investiment->current_balance = $quote['price'];
                    $investiment->logo_url = $quote['logourl'];
                    $investiment->last_price_fetched_at = $now;
                    $investiment->save();
                });
            }
        }

        return Inertia::render('valuations/Index', [
            'valuations' => $investiments->through(fn (Investiment $investiment): array => $this->investmentValuationsPayload($investiment)),
        ]);
    }

    public function show(InvestimentValuation $valuation, BrapiService $brapi)
    {
        $valuation->load(['investiment']);

        if (! $valuation->investiment->type?->isFixedIncome()) {
            $quotes = $brapi->fetchQuotes([$valuation->investiment->name]);
            $quote = $quotes->get(strtoupper(trim($valuation->investiment->name)));

            if ($quote) {
                $valuation->investiment->current_balance = $quote['price'];
                $valuation->investiment->logo_url = $quote['logourl'];
            }
        }

        return Inertia::render('valuations/Show', [
            'valuation' => [
                'id' => $valuation->id,
                'investiment' => [
                    'id' => $valuation->investiment->id,
                    'name' => $valuation->investiment->name,
                    'logo_url' => $valuation->investiment->logo_url,
                ],
                'method' => $valuation->method,
                'method_label' => $valuation->methodLabel(),
                'assumptions' => $valuation->assumptions,
                'projected_cash_flows' => $valuation->projected_cash_flows,
                'summary' => $valuation->summary,
                'calculated_at' => $valuation->calculated_at,
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('valuations/Create', [
            'investiments' => Investiment::query()
                ->orderBy('name')
                ->get(['id', 'name', 'logo_url']),
        ]);
    }

    public function update(
        InvestimentValuationRequest $request,
        InvestimentValuation $valuation,
        DcfValuationService $valuationService,
    ) {
        abort_unless($valuation->method === InvestimentValuation::METHOD_DCF, 404);

        $calculatedValuation = $valuationService->calculate($request->validated());

        $valuation->update([
            'assumptions' => $calculatedValuation['assumptions'],
            'projected_cash_flows' => $calculatedValuation['projected_cash_flows'],
            'summary' => $calculatedValuation['summary'],
            'calculated_at' => now(),
        ]);

        return redirect()->route('valuations.show', $valuation)
            ->with('success', 'Valuation atualizada com sucesso');
    }

    private function investmentValuationsPayload(Investiment $investiment): array
    {
        $dcfValuation = $investiment->valuations
            ->firstWhere('method', InvestimentValuation::METHOD_DCF);
        $precoTetoValuation = $investiment->valuations
            ->firstWhere('method', InvestimentValuation::METHOD_PRECO_TETO);

        return [
            'investiment' => [
                'id' => $investiment->id,
                'name' => $investiment->name,
                'logo_url' => $investiment->logo_url,
            ],
            'dcf' => $dcfValuation ? $this->valuationSummaryPayload($dcfValuation) : null,
            'preco_teto' => $precoTetoValuation ? $this->valuationSummaryPayload($precoTetoValuation) : null,
        ];
    }

    private function valuationSummaryPayload(InvestimentValuation $valuation): array
    {
        $summary = $valuation->summary ?? [];

        return [
            'id' => $valuation->id,
            'method' => $valuation->method,
            'method_label' => $valuation->methodLabel(),
            'calculated_at' => $valuation->calculated_at,
            'fair_value_per_share' => $summary['fair_value_per_share'] ?? $summary['price_ceiling'] ?? null,
            'margin_of_safety' => $summary['margin_of_safety'] ?? null,
            'upside' => $summary['upside'] ?? null,
        ];
    }
}
