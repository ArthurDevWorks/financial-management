<?php

namespace App\Http\Controllers;

use App\Models\Investiment;
use App\Models\Account;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvestimentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('investiments/Index', [
            'investiments' => Investiment::query()
                ->with(['categories'])
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
            'categories' => Category::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'dt_investment' => 'required|date_format:Y-m-d H:i:s',
            'value' => 'required|numeric|min:0',
            'type' => 'required|exists:categories,id',
            'profitability' => 'required|integer',
        ]);

        Investiment::create($validated);

        return redirect()->route('investiments.index')
            ->with('success', 'Investimento cadastrado com sucesso');
    }

    /**
     * Display the specified resource.
     */
    public function show(Investiment $investiment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Investiment $investiment)
    {
        return Inertia::render('investiments/Edit', [
            'investiment' => $investiment->load(['categories']),
            'categories' => Category::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Investiment $investiment)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'dt_investment' => 'required|date_format:Y-m-d H:i:s',
            'value' => 'required|numeric|min:0',
            'type' => 'required|exists:categories,id',
            'profitability' => 'required|integer',
        ]);

        $investiment->update($validated);

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
}
