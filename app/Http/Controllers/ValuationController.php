<?php

namespace App\Http\Controllers;

use App\Http\Requests\InvestimentValuationRequest;
use App\Models\Investiment;
use App\Models\InvestimentValuation;
use App\Services\DcfValuationService;
use Inertia\Inertia;

class ValuationController extends Controller
{
    public function index()
    {
        return Inertia::render('valuations/Index', [
            'valuations' => Investiment::query()
                ->whereHas('valuations')
                ->with(['valuations' => fn ($query) => $query->latest('calculated_at')])
                ->orderBy('name')
                ->paginate(15)
                ->through(fn (Investiment $investiment): array => $this->investmentValuationsPayload($investiment)),
        ]);
    }

    public function show(InvestimentValuation $valuation)
    {
        $valuation->load(['investiment']);

        return Inertia::render('valuations/Show', [
            'valuation' => [
                'id' => $valuation->id,
                'investiment' => [
                    'id' => $valuation->investiment->id,
                    'name' => $valuation->investiment->name,
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
                ->get(['id', 'name']),
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
