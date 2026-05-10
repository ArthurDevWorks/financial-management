<?php

namespace App\Http\Controllers;

use App\Enums\CategoryType;
use App\Http\Requests\InvestimentStoreRequest;
use App\Http\Requests\InvestimentUpdateRequest;
use App\Http\Requests\InvestimentValuationRequest;
use App\Models\Category;
use App\Models\Investiment;
use App\Services\DcfValuationService;
use Inertia\Inertia;

class InvestimentController extends Controller
{
    private const ALLOWED_CATEGORY_NAMES = [
        'ETF',
        'Etf',
        'Ações',
        'Acoes',
        'FIIs',
        'FII',
        'Fiis',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('investiments/Index', [
            'investiments' => Investiment::query()
                ->with(['category'])
                ->orderBy('dt_investment', 'desc')
                ->paginate(10)
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('investiments/Create', [
            'categories' => $this->investmentCategories(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(InvestimentStoreRequest $request)
    {
        Investiment::create($request->validated());

        return redirect()->route('investiments.index')
            ->with('success', 'Investimento cadastrado com sucesso');
    }

    /**
     * Display the specified resource.
     */
    public function show(Investiment $investiment)
    {
        return Inertia::render('investiments/Valuation', [
            'investiment' => $investiment->load(['category']),
            'valuation' => null,
            'defaultAssumptions' => [
                'current_fcf' => '',
                'discount_rate' => '12',
                'terminal_growth_rate' => '3',
                'projection_years' => '5',
                'total_shares' => '',
                'payout' => '75',
                'roe' => '24',
                'current_price_per_share' => (string) $investiment->value,
                'growth_rates' => array_fill(0, 5, '6'),
            ],
        ]);
    }

    public function valuation(
        InvestimentValuationRequest $request,
        Investiment $investiment,
        DcfValuationService $valuationService,
    ) {
        $validated = $request->validated();

        return Inertia::render('investiments/Valuation', [
            'investiment' => $investiment->load(['category']),
            'valuation' => $valuationService->calculate($validated),
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
    public function edit(Investiment $investiment)
    {
        return Inertia::render('investiments/Edit', [
            'investiment' => $investiment->load(['category']),
            'categories' => $this->investmentCategories(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(InvestimentUpdateRequest $request, Investiment $investiment)
    {
        $investiment->update($request->validated());

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

    private function investmentCategories()
    {
        return Category::query()
            ->where('type', CategoryType::INVESTMENT->value)
            ->whereIn('name', self::ALLOWED_CATEGORY_NAMES)
            ->orderBy('name')
            ->get();
    }
}
