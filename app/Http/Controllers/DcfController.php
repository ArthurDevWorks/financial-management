<?php

namespace App\Http\Controllers;

use App\Http\Requests\InvestimentValuationRequest;
use App\Models\Asset;
use App\Models\InvestimentValuation;
use Inertia\Inertia;

class DcfController extends Controller
{
    public function index()
    {
        $assetId = request()->query('asset_id');
        $valuationId = request()->query('valuation_id');

        $valuation = $valuationId
            ? InvestimentValuation::query()
                ->with('asset')
                ->where('method', InvestimentValuation::METHOD_DCF)
                ->findOrFail((int) $valuationId)
            : null;

        $asset = $valuation?->asset
            ?? ($assetId ? Asset::find((int) $assetId) : null);

        $assets = Asset::query()
            ->orderBy('ticker')
            ->get([
                'id', 'ticker', 'name', 'asset_type', 'current_price',
                'logo_url', 'free_cash_flow', 'roe', 'payout',
                'total_shares', 'net_debt_to_ebitda',
            ]);

        return Inertia::render('dcf/Index', [
            'asset' => $asset ? [
                'id' => $asset->id,
                'ticker' => $asset->ticker,
                'name' => $asset->name,
                'current_price' => $asset->current_price,
                'free_cash_flow' => $asset->free_cash_flow,
                'roe' => $asset->roe,
                'payout' => $asset->payout,
                'total_shares' => $asset->total_shares,
                'net_debt_to_ebitda' => $asset->net_debt_to_ebitda,
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
            'defaultAssumptions' => $asset ? $this->buildDcfDefaults($asset, $valuation) : null,
        ]);
    }

    public function store(InvestimentValuationRequest $request)
    {
        $validated = $request->validated();
        $assetId = (int) $validated['asset_id'];

        $existing = InvestimentValuation::where('asset_id', $assetId)
            ->where('method', InvestimentValuation::METHOD_DCF)
            ->first();

        if ($existing) {
            $existing->update([
                'assumptions' => $validated,
                'calculated_at' => now(),
            ]);
        } else {
            InvestimentValuation::create([
                'asset_id' => $assetId,
                'method' => InvestimentValuation::METHOD_DCF,
                'assumptions' => $validated,
                'calculated_at' => now(),
            ]);
        }

        return redirect()->route('valuations.index')
            ->with('success', 'Valuation DCF salva com sucesso');
    }

    public function update(InvestimentValuationRequest $request, InvestimentValuation $valuation)
    {
        abort_unless($valuation->method === InvestimentValuation::METHOD_DCF, 404);

        $validated = $request->validated();

        $valuation->update([
            'assumptions' => $validated,
            'calculated_at' => now(),
        ]);

        return redirect()->route('valuations.show', $valuation)
            ->with('success', 'Valuation DCF atualizada com sucesso');
    }

    private function buildDcfDefaults(Asset $asset, ?InvestimentValuation $valuation): array
    {
        if ($valuation) {
            $a = $valuation->assumptions;
            return [
                'current_fcf' => (string) ($a['current_fcf'] ?? $asset->free_cash_flow ?? ''),
                'roe' => (string) ($a['roe'] ?? $asset->roe ?? '15'),
                'payout' => (string) ($a['payout'] ?? $asset->payout ?? '50'),
                'discount_rate' => (string) ($a['discount_rate'] ?? '12.5'),
                'terminal_growth_rate' => (string) ($a['terminal_growth_rate'] ?? '3'),
                'projection_years' => (string) ($a['projection_years'] ?? '5'),
                'total_shares' => (string) ($a['total_shares'] ?? $asset->total_shares ?? ''),
                'current_price_per_share' => (string) ($a['current_price_per_share'] ?? $asset->current_price ?? ''),
                'growth_rates' => $a['growth_rates'] ?? [],
            ];
        }

        $roe = (float) ($asset->roe ?? 15);
        $payout = (float) ($asset->payout ?? 50);
        $defaultGrowth = round((1 - $payout / 100) * $roe, 2);

        return [
            'current_fcf' => (string) ($asset->free_cash_flow ?? ''),
            'roe' => (string) ($asset->roe ?? '15'),
            'payout' => (string) ($asset->payout ?? '50'),
            'discount_rate' => '12.5',
            'terminal_growth_rate' => '3',
            'projection_years' => '5',
            'total_shares' => (string) ($asset->total_shares ?? ''),
            'current_price_per_share' => (string) ($asset->current_price ?? ''),
            'growth_rates' => array_fill(0, 5, $defaultGrowth ?: 5),
        ];
    }
}
