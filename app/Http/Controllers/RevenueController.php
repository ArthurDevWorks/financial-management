<?php

namespace App\Http\Controllers;

use App\Http\Requests\RevenueStoreRequest;
use App\Http\Requests\RevenueUpdateRequest;
use App\Models\Revenue;
use App\Models\Account;
use App\Models\Category;
use Inertia\Inertia;

class RevenueController extends Controller
{
    public function index()
    {
        return Inertia::render('revenues/Index', [
            'revenues' => Revenue::query()
                ->with(['account', 'category'])
                ->orderBy('dt_revenue', 'desc')
                ->paginate(10)
        ]);
    }

    public function create()
    {
        return Inertia::render('revenues/Create', [
            'accounts' => Account::all(),
            'categories' => Category::where('type', 'revenue')->get(),
        ]);
    }

    public function store(RevenueStoreRequest $request)
    {
        Revenue::create($request->validated());

        return redirect()->route('revenues.index')
            ->with('success', 'Receita cadastrada com sucesso');
    }

    public function edit(Revenue $revenue)
    {
        return Inertia::render('revenues/Edit', [
            'revenue' => $revenue->load(['account', 'category']),
            'accounts' => Account::all(),
            'categories' => Category::where('type', 'revenue')->get(),
        ]);
    }

    public function update(RevenueUpdateRequest $request, Revenue $revenue)
    {
        $revenue->update($request->validated());

        return redirect()->route('revenues.index')
            ->with('success', 'Receita atualizada com sucesso');
    }

    public function destroy(Revenue $revenue)
    {
        $revenue->delete();

        return redirect()->route('revenues.index')
            ->with('success', 'Receita removida com sucesso');
    }
}
