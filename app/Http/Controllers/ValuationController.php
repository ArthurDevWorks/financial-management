<?php

namespace App\Http\Controllers;

use App\Models\Investiment;
use App\Models\InvestimentValuation;
use Inertia\Inertia;

class ValuationController extends Controller
{
    public function index()
    {
        return Inertia::render('valuations/Index', [
            'valuations' => InvestimentValuation::query()
                ->with(['investiment'])
                ->orderBy('calculated_at', 'desc')
                ->paginate(15),
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
}
