<?php

namespace App\Http\Controllers;

use App\Http\Requests\PrecoTetoValuationRequest;
use App\Models\Asset;
use App\Models\InvestimentValuation;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PrecoTetoController extends Controller
{
    public function index()
    {
        $assetId = request()->query('asset_id');
        $valuationId = request()->query('valuation_id');

        $valuation = $valuationId
            ? InvestimentValuation::query()
                ->with('asset')
                ->where('user_id', Auth::id())
                ->where('method', InvestimentValuation::METHOD_PRECO_TETO)
                ->findOrFail((int) $valuationId)
            : null;

        $asset = $valuation?->asset
            ?? ($assetId ? Asset::find((int) $assetId) : null);

        $assets = Asset::query()
            ->orderBy('ticker')
            ->get(['id', 'ticker', 'name', 'asset_type', 'current_price', 'logo_url', 'net_income', 'total_shares', 'roe', 'payout']);

        return Inertia::render('preco-teto/Index', [
            'asset' => $asset ? [
                'id' => $asset->id,
                'ticker' => $asset->ticker,
                'name' => $asset->name,
                'current_price' => $asset->current_price,
                'net_income' => $asset->net_income,
                'total_shares' => $asset->total_shares,
                'roe' => $asset->roe,
                'payout' => $asset->payout,
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
            'defaultAssumptions' => $asset ? $this->buildPrecoTetoDefaults($asset, $valuation) : null,
        ]);
    }

    public function store(PrecoTetoValuationRequest $request)
    {
        $validated = $request->validated();
        $assetId = (int) $validated['asset_id'];

        $existing = InvestimentValuation::where('user_id', Auth::id())
            ->where('asset_id', $assetId)
            ->where('method', InvestimentValuation::METHOD_PRECO_TETO)
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
                'method' => InvestimentValuation::METHOD_PRECO_TETO,
                'assumptions' => $validated,
                'calculated_at' => now(),
            ]);
        }

        return redirect()->back()
            ->with('success', 'Valuation de Preço Teto salva com sucesso');
    }

    public function update(PrecoTetoValuationRequest $request, InvestimentValuation $valuation)
    {
        abort_unless($valuation->method === InvestimentValuation::METHOD_PRECO_TETO, 404);
        abort_unless($valuation->user_id === Auth::id(), 403);

        $validated = $request->validated();

        $valuation->update([
            'assumptions' => $validated,
            'calculated_at' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'Valuation de Preço Teto atualizada com sucesso');
    }

    private function buildPrecoTetoDefaults(Asset $asset, ?InvestimentValuation $valuation): array
    {
        if ($valuation) {
            $a = $valuation->assumptions;

            return [
                'desired_yield' => (string) ($a['desired_yield'] ?? '8'),
                'projected_payout' => (string) ($a['projected_payout'] ?? '50'),
                'projected_net_income' => (string) ($a['projected_net_income'] ?? $asset->net_income ?? ''),
                'total_shares' => (string) ($a['total_shares'] ?? $asset->total_shares ?? ''),
                'projected_growth_rate' => (string) ($a['projected_growth_rate'] ?? '5'),
                'current_price_per_share' => (string) ($a['current_price_per_share'] ?? $asset->current_price ?? ''),
                'roe' => (string) ($a['roe'] ?? $asset->roe ?? '0'),
                'payout' => (string) ($a['payout'] ?? $asset->payout ?? '0'),
            ];
        }

        return [
            'desired_yield' => '8',
            'projected_payout' => '50',
            'projected_net_income' => (string) ($asset->net_income ?? ''),
            'total_shares' => (string) ($asset->total_shares ?? ''),
            'projected_growth_rate' => '5',
            'current_price_per_share' => (string) ($asset->current_price ?? ''),
            'roe' => (string) ($asset->roe ?? '0'),
            'payout' => (string) ($asset->payout ?? '0'),
        ];
    }
}
