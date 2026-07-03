<?php

namespace App\Http\Controllers;

use App\Http\Requests\PrecoTetoValuationRequest;
use App\Models\Investiment;
use App\Models\InvestimentValuation;
use App\Services\BrapiService;
use App\Services\PrecoTetoProjetivoValuationService;
use Inertia\Inertia;

class PrecoTetoController extends Controller
{
    public function index(BrapiService $brapi)
    {
        $investimentId = request()->query('investiment_id');
        $valuationId = request()->query('valuation_id');

        /** @var InvestimentValuation|null $valuation */
        $valuation = $valuationId
            ? InvestimentValuation::query()
                ->with(['investiment'])
                ->where('method', InvestimentValuation::METHOD_PRECO_TETO)
                ->findOrFail((int) $valuationId)
            : null;

        $investiment = $valuation?->investiment
            ?? ($investimentId
                ? Investiment::find((int) $investimentId, ['id', 'name', 'value', 'current_balance', 'logo_url', 'type'])
                : null);

        if ($investiment && ! $investiment->type?->isFixedIncome()) {
            $quotes = $brapi->fetchQuotes([$investiment->name]);
            $quote = $quotes->get(strtoupper($investiment->name));

            if ($quote) {
                $investiment->current_balance = $quote['price'];
                $investiment->logo_url = $quote['logourl'];
            }
        }

        $investiments = Investiment::query()
            ->orderBy('name')
            ->get(['id', 'name', 'value', 'current_balance', 'logo_url', 'type']);

        return Inertia::render('preco-teto/Index', [
            'investiment' => $investiment ? [
                'id' => $investiment->id,
                'name' => $investiment->name,
                'value' => $investiment->value,
                'current_balance' => $investiment->current_balance,
                'logo_url' => $investiment->logo_url,
            ] : null,
            'investiments' => $investiments->map(fn (Investiment $i): array => [
                'id' => $i->id,
                'name' => $i->name,
                'value' => $i->value,
                'current_balance' => $i->current_balance,
                'logo_url' => $i->logo_url,
            ]),
            'valuation' => $valuation ? $this->valuationPayload($valuation) : null,
        ]);
    }

    public function store(
        PrecoTetoValuationRequest $request,
        PrecoTetoProjetivoValuationService $valuationService,
    ) {
        $validated = $request->validated();
        $calculatedValuation = $valuationService->calculate($validated);

        $valuation = Investiment::findOrFail((int) $validated['investiment_id'])
            ->valuations()
            ->create([
                'method' => InvestimentValuation::METHOD_PRECO_TETO,
                'assumptions' => $calculatedValuation['assumptions'],
                'projected_cash_flows' => $calculatedValuation['projected_cash_flows'],
                'summary' => $calculatedValuation['summary'],
                'calculated_at' => now(),
            ]);

        return redirect()->route('valuations.show', $valuation)
            ->with('success', 'Valuation de Preço Teto salva com sucesso');
    }

    public function update(
        PrecoTetoValuationRequest $request,
        InvestimentValuation $valuation,
        PrecoTetoProjetivoValuationService $valuationService,
    ) {
        abort_unless($valuation->method === InvestimentValuation::METHOD_PRECO_TETO, 404);

        $calculatedValuation = $valuationService->calculate($request->validated());

        $valuation->update([
            'assumptions' => $calculatedValuation['assumptions'],
            'projected_cash_flows' => $calculatedValuation['projected_cash_flows'],
            'summary' => $calculatedValuation['summary'],
            'calculated_at' => now(),
        ]);

        return redirect()->route('valuations.show', $valuation)
            ->with('success', 'Valuation de Preço Teto atualizada com sucesso');
    }

    private function valuationPayload(InvestimentValuation $valuation): array
    {
        return [
            'id' => $valuation->id,
            'method' => $valuation->method,
            'assumptions' => $valuation->assumptions,
            'summary' => $valuation->summary,
            'calculated_at' => $valuation->calculated_at,
        ];
    }
}
