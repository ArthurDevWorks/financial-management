<?php

namespace App\Http\Controllers;

use App\Enums\InvestmentAssetType;
use App\Http\Requests\InvestimentStoreRequest;
use App\Http\Requests\InvestimentUpdateRequest;
use App\Models\Investiment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvestimentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return Inertia::render('investiments/Index', [
            'investiments' => Investiment::query()
                ->when($request->search, fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
                ->orderBy('id', 'desc')
                ->paginate(10)
                ->through(fn (Investiment $investiment): array => $this->investmentPayload($investiment)),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('investiments/Create', $this->formOptions());
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
        return redirect()->route('investiments.edit', $investiment);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Investiment $investiment)
    {
        return Inertia::render('investiments/Edit', [
            'investiment' => $this->investmentPayload($investiment),
            ...$this->formOptions(),
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

    private function formOptions(): array
    {
        return [
            'assetTypes' => array_values(array_filter(
                InvestmentAssetType::options(),
                static fn (array $assetType): bool => $assetType['value'] === InvestmentAssetType::STOCK->value,
            )),
        ];
    }

    private function investmentPayload(Investiment $investiment): array
    {
        $assetType = $investiment->type;

        return [
            'id' => $investiment->id,
            'name' => $investiment->name,
            'type' => $assetType?->value,
            'type_label' => $assetType?->label() ?? 'Ações',
            'value' => (float) ($investiment->value ?? 0),
            'current_balance' => (float) ($investiment->current_balance ?? 0),
        ];
    }
}
