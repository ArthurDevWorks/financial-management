<?php

namespace App\Http\Controllers;

use App\Http\Requests\GordonValuationRequest;
use App\Models\Asset;
use App\Models\InvestimentValuation;
use Inertia\Inertia;

class GordonController extends Controller
{
    public function index()
    {
        $assetId = request()->query('asset_id');
        $valuationId = request()->query('valuation_id');

        $valuation = $valuationId
            ? InvestimentValuation::query()
                ->with('asset')
                ->where('method', InvestimentValuation::METHOD_GORDON)
                ->findOrFail((int) $valuationId)
            : null;

        $asset = $valuation?->asset
            ?? ($assetId ? Asset::find((int) $assetId) : null);

        $assets = Asset::query()
            ->orderBy('ticker')
            ->get(['id', 'ticker', 'name', 'asset_type', 'current_price', 'logo_url', 'dividends_per_share']);

        return Inertia::render('gordon/Index', [
            'asset' => $asset ? [
                'id' => $asset->id,
                'ticker' => $asset->ticker,
                'name' => $asset->name,
                'current_price' => $asset->current_price,
                'dividends_per_share' => $asset->dividends_per_share,
                'logo_url' => $asset->logo_url,
                'asset_type' => $asset->asset_type,
            ] : null,
            'assets' => $assets->map(fn (Asset $a): array => [
                'id' => $a->id,
                'ticker' => $a->ticker,
                'name' => $a->name,
                'current_price' => $a->current_price,
                'logo_url' => $a->logo_url,
            ]),
            'valuation' => $valuation ? [
                'id' => $valuation->id,
                'assumptions' => $valuation->assumptions,
                'calculated_at' => $valuation->calculated_at,
            ] : null,
            'defaultAssumptions' => $asset ? $this->buildGordonDefaults($asset, $valuation) : null,
        ]);
    }

    public function store(GordonValuationRequest $request)
    {
        $validated = $request->validated();

        InvestimentValuation::create([
            'asset_id' => (int) $validated['asset_id'],
            'method' => InvestimentValuation::METHOD_GORDON,
            'assumptions' => $validated,
            'calculated_at' => now(),
        ]);

        return redirect()->route('valuations.index')
            ->with('success', 'Valuation de Gordon salva com sucesso');
    }

    public function update(GordonValuationRequest $request, InvestimentValuation $valuation)
    {
        abort_unless($valuation->method === InvestimentValuation::METHOD_GORDON, 404);

        $validated = $request->validated();

        $valuation->update([
            'assumptions' => $validated,
            'calculated_at' => now(),
        ]);

        return redirect()->route('valuations.show', $valuation)
            ->with('success', 'Valuation de Gordon atualizada com sucesso');
    }

    private function buildGordonDefaults(Asset $asset, ?InvestimentValuation $valuation): array
    {
        if ($valuation) {
            $a = $valuation->assumptions;
            return [
                'dps' => (string) ($a['dps'] ?? $asset->dividends_per_share ?? ''),
                'discount_rate' => (string) ($a['discount_rate'] ?? '12.5'),
                'growth_perpetuity' => (string) ($a['growth_perpetuity'] ?? '3'),
                'current_price' => (string) ($a['current_price'] ?? $asset->current_price ?? ''),
                'projection_years' => (string) ($a['projection_years'] ?? '5'),
                'growth_rates' => $a['growth_rates'] ?? [8.0, 7.0, 6.0, 5.0, 4.0],
            ];
        }

        return [
            'dps' => (string) ($asset->dividends_per_share ?? ''),
            'discount_rate' => '12.5',
            'growth_perpetuity' => '3',
            'current_price' => (string) ($asset->current_price ?? ''),
            'projection_years' => '5',
            'growth_rates' => [8.0, 7.0, 6.0, 5.0, 4.0],
        ];
    }
}
