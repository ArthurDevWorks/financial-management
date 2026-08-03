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
        $assets = Asset::query()
            ->whereHas('valuations')
            ->with(['valuations' => fn ($q) => $q->orderByDesc('calculated_at')])
            ->orderByDesc(
                InvestimentValuation::query()
                    ->select('calculated_at')
                    ->whereColumn('asset_id', 'assets.id')
                    ->orderByDesc('calculated_at')
                    ->limit(1)
            )
            ->paginate(12);

        return Inertia::render('valuations/Index', [
            'valuations' => $assets->through(fn (Asset $asset): array => [
                'id' => $asset->id,
                'ticker' => $asset->ticker,
                'name' => $asset->name,
                'logo_url' => $asset->logo_url,
                'current_price' => $asset->current_price,
                'asset_type' => $asset->asset_type,
                'valuation_count' => $asset->valuations->count(),
                'latest_calculated_at' => $asset->valuations->first()?->calculated_at,
                'valuations' => $asset->valuations->map(fn (InvestimentValuation $v): array => [
                    'id' => $v->id,
                    'method' => $v->method,
                    'method_label' => $v->methodLabel(),
                    'calculated_at' => $v->calculated_at,
                    ...self::computeSummary($v),
                ])->values(),
            ]),
        ]);
    }

    private static function computeSummary(InvestimentValuation $v): array
    {
        $a = $v->assumptions ?? [];
        $price = (float) ($v->asset->current_price ?? 0);

        if ($v->method === InvestimentValuation::METHOD_PRECO_TETO) {
            $dy = ((float) ($a['desired_yield'] ?? 0)) / 100;
            $payout = ((float) ($a['projected_payout'] ?? 0)) / 100;
            $lucro = (float) ($a['projected_net_income'] ?? 0);
            $shares = (float) ($a['total_shares'] ?? 0);
            $growth = ((float) ($a['projected_growth_rate'] ?? 0)) / 100;

            $lucroProj = $lucro * (1 + $growth);
            $lpa = $shares > 0 ? $lucroProj / $shares : 0;
            $dpa = $payout * $lpa;
            $fairValue = $dy > 0 ? $dpa / $dy : 0;
        } elseif ($v->method === InvestimentValuation::METHOD_GORDON) {
            $dps = (float) ($a['dps'] ?? 0);
            $ke = ((float) ($a['discount_rate'] ?? 0)) / 100;
            $riskPremium = ((float) ($a['risk_premium'] ?? 0)) / 100;
            $g = ((float) ($a['growth_perpetuity'] ?? 0)) / 100;
            $effectiveKe = $ke + $riskPremium;

            $fairValue = $effectiveKe > $g && $dps > 0 ? $dps / ($effectiveKe - $g) : 0;
        } else {
            $fcf = (float) ($a['current_fcf'] ?? 0);
            $rates = $a['growth_rates'] ?? [];
            $ke = ((float) ($a['discount_rate'] ?? 0)) / 100;
            $gTerminal = ((float) ($a['terminal_growth_rate'] ?? 0)) / 100;
            $years = (int) ($a['projection_years'] ?? 5);
            $shares = (float) ($a['total_shares'] ?? 0);

            $projectedFcfs = [];
            $currentFcf = $fcf;
            for ($i = 0; $i < $years; $i++) {
                $gr = ((float) ($rates[$i] ?? 0)) / 100;
                $currentFcf *= (1 + $gr);
                $pv = $ke > 0 ? $currentFcf / pow(1 + $ke, $i + 1) : 0;
                $projectedFcfs[] = $pv;
            }

            $lastFcf = $currentFcf;
            $terminalValue = $ke > $gTerminal ? ($lastFcf * (1 + $gTerminal)) / ($ke - $gTerminal) : 0;
            $pvTerminal = $ke > 0 ? $terminalValue / pow(1 + $ke, $years) : 0;
            $enterpriseValue = array_sum($projectedFcfs) + $pvTerminal;

            $fairValue = $shares > 0 ? $enterpriseValue / $shares : 0;
        }

        $marginOfSafety = $fairValue > 0 && $price > 0
            ? (($fairValue - $price) / $fairValue) * 100
            : null;
        $upside = $price > 0 && $fairValue > 0
            ? (($fairValue - $price) / $price) * 100
            : null;

        return [
            'fair_value' => $fairValue > 0 ? round($fairValue, 2) : null,
            'margin_of_safety' => $marginOfSafety !== null ? round($marginOfSafety, 2) : null,
            'upside' => $upside !== null ? round($upside, 2) : null,
        ];
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
