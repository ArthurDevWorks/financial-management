<?php

namespace App\Http\Controllers;

use App\Enums\CategoryType;
use App\Http\Requests\InvestimentStoreRequest;
use App\Http\Requests\InvestimentUpdateRequest;
use App\Http\Requests\InvestimentValuationRequest;
use App\Models\Category;
use App\Models\Investiment;
use App\Models\InvestimentValuation;
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
        /** @var InvestimentValuation|null $lastValuation */
        $lastValuation = $investiment->valuations()
            ->latest('calculated_at')
            ->first();

        return Inertia::render('investiments/Valuation', [
            'investiment' => $investiment->load(['category']),
            'valuation' => $lastValuation ? [
                'assumptions' => $lastValuation->assumptions,
                'projected_cash_flows' => $lastValuation->projected_cash_flows,
                'summary' => $lastValuation->summary,
            ] : null,
            'defaultAssumptions' => $this->buildDefaultAssumptions($investiment, $lastValuation),
        ]);
    }

    public function valuation(
        InvestimentValuationRequest $request,
        Investiment $investiment,
        DcfValuationService $valuationService,
    ) {
        $validated = $request->validated();
        $calculatedValuation = $valuationService->calculate($validated);

        $investiment->valuations()->create([
            'assumptions' => $calculatedValuation['assumptions'],
            'projected_cash_flows' => $calculatedValuation['projected_cash_flows'],
            'summary' => $calculatedValuation['summary'],
            'calculated_at' => now(),
        ]);

        return Inertia::render('investiments/Valuation', [
            'investiment' => $investiment->load(['category']),
            'valuation' => $calculatedValuation,
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

    private function buildDefaultAssumptions(
        Investiment $investiment,
        ?InvestimentValuation $lastValuation,
    ): array {
        if ($lastValuation) {
            $assumptions = $lastValuation->assumptions;

            return [
                'current_fcf' => (string) ($assumptions['current_fcf'] ?? ''),
                'discount_rate' => (string) ($assumptions['discount_rate'] ?? '12'),
                'terminal_growth_rate' => (string) ($assumptions['terminal_growth_rate'] ?? '3'),
                'projection_years' => (string) ($assumptions['projection_years'] ?? '5'),
                'total_shares' => (string) ($assumptions['total_shares'] ?? ''),
                'payout' => (string) ($assumptions['payout'] ?? '75'),
                'roe' => (string) ($assumptions['roe'] ?? '24'),
                'current_price_per_share' => (string) ($assumptions['current_price_per_share'] ?? $investiment->value),
                'growth_rates' => array_map(
                    static fn (mixed $value): string => (string) $value,
                    $assumptions['growth_rates'] ?? array_fill(0, 5, '6'),
                ),
            ];
        }

        return [
            'current_fcf' => '',
            'discount_rate' => '12',
            'terminal_growth_rate' => '3',
            'projection_years' => '5',
            'total_shares' => '',
            'payout' => '75',
            'roe' => '24',
            'current_price_per_share' => (string) $investiment->value,
            'growth_rates' => array_fill(0, 5, '6'),
        ];
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
