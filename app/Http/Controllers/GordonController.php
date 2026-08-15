<?php

namespace App\Http\Controllers;

use App\Http\Requests\GordonValuationRequest;
use App\Models\Asset;
use App\Models\InvestimentValuation;
use Illuminate\Support\Facades\Auth;
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
                ->where('user_id', Auth::id())
                ->where('method', InvestimentValuation::METHOD_GORDON)
                ->findOrFail((int) $valuationId)
            : null;

        $asset = $valuation?->asset
            ?? ($assetId ? Asset::find((int) $assetId) : null);

        $assets = Asset::query()
            ->orderBy('ticker')
            ->get(['id', 'ticker', 'name', 'asset_type', 'current_price', 'logo_url', 'dividends_per_share']);

        $assetFields = $asset ? [
            'id' => $asset->id,
            'ticker' => $asset->ticker,
            'name' => $asset->name,
            'current_price' => $asset->current_price,
            'market_cap' => $asset->market_cap,
            'dividend_yield' => $asset->dividend_yield,
            'dividends_per_share' => $asset->dividends_per_share,
            'price_to_book' => $asset->price_to_book,
            'roe' => $asset->roe,
            'payout' => $asset->payout,
            'net_income' => $asset->net_income,
            'total_shares' => $asset->total_shares,
            'free_cash_flow' => $asset->free_cash_flow,
            'net_debt_to_ebitda' => $asset->net_debt_to_ebitda,
            'logo_url' => $asset->logo_url,
            'asset_type' => $asset->asset_type,
        ] : null;

        return Inertia::render('gordon/Index', [
            'asset' => $assetFields,
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
        $assetId = (int) $validated['asset_id'];

        $existing = InvestimentValuation::where('user_id', Auth::id())
            ->where('asset_id', $assetId)
            ->where('method', InvestimentValuation::METHOD_GORDON)
            ->first();

        if ($existing) {
            $existing->update([
                'assumptions' => $validated,
                'calculated_at' => now(),
            ]);
        } else {
            InvestimentValuation::create([
                'user_id' => Auth::id(),
                'asset_id' => $assetId,
                'method' => InvestimentValuation::METHOD_GORDON,
                'assumptions' => $validated,
                'calculated_at' => now(),
            ]);
        }

        return redirect()->back()
            ->with('success', 'Valuation de Gordon salva com sucesso');
    }

    public function update(GordonValuationRequest $request, InvestimentValuation $valuation)
    {
        abort_unless($valuation->method === InvestimentValuation::METHOD_GORDON, 404);
        abort_unless($valuation->user_id === Auth::id(), 403);

        $validated = $request->validated();

        $valuation->update([
            'assumptions' => $validated,
            'calculated_at' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'Valuation de Gordon atualizada com sucesso');
    }

    private function buildGordonDefaults(Asset $asset, ?InvestimentValuation $valuation): array
    {
        if ($valuation) {
            $a = $valuation->assumptions;

            return [
                'dps' => (string) ($a['dps'] ?? $asset->dividends_per_share ?? ''),
                'discount_rate' => (string) ($a['discount_rate'] ?? '13'),
                'risk_premium' => (string) ($a['risk_premium'] ?? '4'),
                'growth_perpetuity' => (string) ($a['growth_perpetuity'] ?? '3'),
                'current_price' => (string) ($a['current_price'] ?? $asset->current_price ?? ''),
                'projection_years' => (string) ($a['projection_years'] ?? '5'),
                'growth_rates' => $a['growth_rates'] ?? [8.0, 7.0, 6.0, 5.0, 4.0],
            ];
        }

        return [
            'dps' => (string) ($asset->dividends_per_share ?? ''),
            'discount_rate' => '13',
            'risk_premium' => '4',
            'growth_perpetuity' => '3',
            'current_price' => (string) ($asset->current_price ?? ''),
            'projection_years' => '5',
            'growth_rates' => [8.0, 7.0, 6.0, 5.0, 4.0],
        ];
    }
}
