<?php

namespace App\Http\Controllers;

use App\Http\Requests\InvestimentValuationRequest;
use App\Models\Asset;
use App\Models\InvestimentValuation;
use App\Services\DcfValuationService;
use Inertia\Inertia;

class ValuationController extends Controller
{
    public function index()
    {
        $valuations = InvestimentValuation::query()
            ->with('asset')
            ->orderBy('calculated_at', 'desc')
            ->paginate(15);

        return Inertia::render('valuations/Index', [
            'valuations' => $valuations->through(fn (InvestimentValuation $v): array => [
                'id' => $v->id,
                'asset' => [
                    'id' => $v->asset->id,
                    'ticker' => $v->asset->ticker,
                    'name' => $v->asset->name,
                    'logo_url' => $v->asset->logo_url,
                    'current_price' => $v->asset->current_price,
                    'asset_type' => $v->asset->asset_type,
                ],
                'method' => $v->method,
                'method_label' => $v->methodLabel(),
                'calculated_at' => $v->calculated_at,
            ]),
        ]);
    }

    public function show(InvestimentValuation $valuation)
    {
        $valuation->load('asset');

        return Inertia::render('valuations/Show', [
            'valuation' => [
                'id' => $valuation->id,
                'asset' => [
                    'id' => $valuation->asset->id,
                    'ticker' => $valuation->asset->ticker,
                    'name' => $valuation->asset->name,
                    'logo_url' => $valuation->asset->logo_url,
                    'current_price' => $valuation->asset->current_price,
                    'dividends_per_share' => $valuation->asset->dividends_per_share,
                    'net_income' => $valuation->asset->net_income,
                    'total_shares' => $valuation->asset->total_shares,
                    'free_cash_flow' => $valuation->asset->free_cash_flow,
                    'asset_type' => $valuation->asset->asset_type,
                ],
                'method' => $valuation->method,
                'method_label' => $valuation->methodLabel(),
                'assumptions' => $valuation->assumptions,
                'calculated_at' => $valuation->calculated_at,
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('valuations/Create', [
            'assets' => Asset::query()
                ->orderBy('ticker')
                ->get(['id', 'ticker', 'name', 'logo_url', 'asset_type', 'current_price']),
        ]);
    }

    public function store(InvestimentValuationRequest $request, DcfValuationService $valuationService)
    {
        $validated = $request->validated();
        $asset = Asset::findOrFail((int) $validated['asset_id']);

        InvestimentValuation::create([
            'asset_id' => $asset->id,
            'method' => InvestimentValuation::METHOD_DCF,
            'assumptions' => $validated,
            'calculated_at' => now(),
        ]);

        return redirect()->route('valuations.index')
            ->with('success', 'Valuation salva com sucesso');
    }

    public function update(
        InvestimentValuationRequest $request,
        InvestimentValuation $valuation,
    ) {
        abort_unless($valuation->method === InvestimentValuation::METHOD_DCF, 404);

        $validated = $request->validated();

        $valuation->update([
            'assumptions' => $validated,
            'calculated_at' => now(),
        ]);

        return redirect()->route('valuations.show', $valuation)
            ->with('success', 'Valuation atualizada com sucesso');
    }
}
