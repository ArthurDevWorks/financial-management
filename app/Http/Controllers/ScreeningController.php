<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetFavorite;
use App\Models\InvestimentValuation;
use App\Models\ScreeningFilter;
use App\Services\BrapiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ScreeningController extends Controller
{
    public function index(Request $request)
    {
        $query = Asset::query();

        if ($request->filled('asset_type') && $request->asset_type !== 'todos') {
            $query->where('asset_type', $request->asset_type);
        }

        if ($request->filled('sector') && $request->sector !== 'todos') {
            $query->where('sector', $request->sector);
        }

        if ($request->filled('dy_min')) {
            $query->where('dividend_yield', '>=', (float) $request->dy_min);
        }

        if ($request->filled('pe_max')) {
            $query->where('price_to_earnings', '<=', (float) $request->pe_max);
        }

        if ($request->filled('roe_min')) {
            $query->where('roe', '>=', (float) $request->roe_min);
        }

        if ($request->filled('pvp_min')) {
            $query->where('price_to_book', '>=', (float) $request->pvp_min);
        }

        if ($request->filled('pvp_max')) {
            $query->where('price_to_book', '<=', (float) $request->pvp_max);
        }

        if ($request->filled('net_debt_to_ebitda_min')) {
            $query->where('net_debt_to_ebitda', '>=', (float) $request->net_debt_to_ebitda_min);
        }

        if ($request->filled('net_debt_to_ebitda_max')) {
            $query->where('net_debt_to_ebitda', '<=', (float) $request->net_debt_to_ebitda_max);
        }

        if ($request->filled('liq_min')) {
            $query->where('volume_avg_30d', '>=', (float) $request->liq_min);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticker', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $stats = [
            'total' => (clone $query)->count(),
            'avg_dy' => (clone $query)->avg('dividend_yield'),
            'avg_pe' => (clone $query)->avg('price_to_earnings'),
            'avg_roe' => (clone $query)->avg('roe'),
        ];

        $assets = $query->orderBy('market_cap', 'desc')
            ->paginate(20)
            ->withQueryString();

        $sectors = Asset::whereNotNull('sector')
            ->distinct()
            ->pluck('sector')
            ->sort()
            ->values();

        $savedFilters = ScreeningFilter::where('user_id', Auth::id())->get();

        $favorites = AssetFavorite::where('user_id', Auth::id())
            ->pluck('ticker')
            ->toArray();

        return Inertia::render('screening/Index', [
            'assets' => $assets,
            'sectors' => $sectors,
            'savedFilters' => $savedFilters,
            'stats' => $stats,
            'favorites' => $favorites,
            'filters' => $request->only([
                'asset_type', 'sector', 'dy_min', 'pe_max', 'roe_min',
                'pvp_min', 'pvp_max', 'net_debt_to_ebitda_min', 'net_debt_to_ebitda_max',
                'liq_min', 'search',
            ]),
        ]);
    }

    public function json(Request $request)
    {
        $query = Asset::query();

        if ($request->filled('asset_type') && $request->asset_type !== 'todos') {
            $query->where('asset_type', $request->asset_type);
        }

        if ($request->filled('sector') && $request->sector !== 'todos') {
            $query->where('sector', $request->sector);
        }

        if ($request->filled('dy_min')) {
            $query->where('dividend_yield', '>=', (float) $request->dy_min);
        }

        if ($request->filled('pe_max')) {
            $query->where('price_to_earnings', '<=', (float) $request->pe_max);
        }

        if ($request->filled('roe_min')) {
            $query->where('roe', '>=', (float) $request->roe_min);
        }

        if ($request->filled('pvp_min')) {
            $query->where('price_to_book', '>=', (float) $request->pvp_min);
        }

        if ($request->filled('pvp_max')) {
            $query->where('price_to_book', '<=', (float) $request->pvp_max);
        }

        if ($request->filled('net_debt_to_ebitda_min')) {
            $query->where('net_debt_to_ebitda', '>=', (float) $request->net_debt_to_ebitda_min);
        }

        if ($request->filled('net_debt_to_ebitda_max')) {
            $query->where('net_debt_to_ebitda', '<=', (float) $request->net_debt_to_ebitda_max);
        }

        if ($request->filled('liq_min')) {
            $query->where('volume_avg_30d', '>=', (float) $request->liq_min);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticker', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $page = max(1, (int) $request->input('page', 1));
        $perPage = 20;

        $assets = $query->orderBy('market_cap', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json($assets);
    }

    public function show(string $ticker, BrapiService $brapi)
    {
        $asset = Asset::where('ticker', strtoupper($ticker))->firstOrFail();

        $isFavorite = AssetFavorite::where('user_id', Auth::id())
            ->where('ticker', $asset->ticker)
            ->exists();

        $dividends = $brapi->fetchHistoricalDividends($ticker);
        $historicalPrices = $brapi->fetchHistoricalPrices($ticker);

        return Inertia::render('screening/AssetDetail', [
            'asset' => $asset,
            'isFavorite' => $isFavorite,
            'dividends' => $dividends->values(),
            'historicalPrices' => $historicalPrices->values(),
        ]);
    }

    public function compare(Request $request)
    {
        $tickers = collect(explode(',', $request->tickers))
            ->map(fn ($t) => strtoupper(trim($t)))
            ->filter()
            ->take(5);

        $assets = Asset::whereIn('ticker', $tickers)->get()->keyBy('ticker');

        return Inertia::render('screening/Compare', [
            'assets' => $assets,
            'tickers' => $tickers->values(),
        ]);
    }

    public function valuation(Request $request, string $ticker)
    {
        $asset = Asset::where('ticker', strtoupper($ticker))->firstOrFail();

        $valuations = InvestimentValuation::query()
            ->where('asset_id', $asset->id)
            ->get();

        $existingValuations = [];
        foreach ($valuations as $v) {
            $existingValuations[$v->method] = [
                'id' => $v->id,
                'assumptions' => $v->assumptions,
            ];
        }

        return Inertia::render('screening/Valuation', [
            'asset' => $asset,
            'existingValuations' => $existingValuations,
            'valuationId' => $request->query('valuation_id'),
        ]);
    }

    public function toggleFavorite(Request $request)
    {
        $validated = $request->validate([
            'ticker' => 'required|string|max:20',
            'asset_type' => 'required|string|max:20',
        ]);

        $favorite = AssetFavorite::where('user_id', Auth::id())
            ->where('ticker', strtoupper($validated['ticker']))
            ->first();

        if ($favorite) {
            $favorite->delete();
            return back()->with('success', 'Removido dos favoritos.');
        }

        AssetFavorite::create([
            'user_id' => Auth::id(),
            'ticker' => strtoupper($validated['ticker']),
            'asset_type' => $validated['asset_type'],
        ]);

        return back()->with('success', 'Adicionado aos favoritos.');
    }

    public function saveFilter(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'criteria' => 'required|array',
        ]);

        ScreeningFilter::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'name' => $validated['name'],
            ],
            [
                'criteria' => $validated['criteria'],
            ]
        );

        return back()->with('success', 'Filtro salvo com sucesso.');
    }

    public function deleteFilter(ScreeningFilter $filter)
    {
        if ($filter->user_id !== Auth::id()) {
            abort(403);
        }
        $filter->delete();

        return back()->with('success', 'Filtro removido.');
    }
}
