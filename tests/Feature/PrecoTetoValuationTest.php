<?php

use App\Models\Investiment;
use App\Models\InvestimentValuation;
use App\Models\User;

it('salva a simulação de preço teto projetivo com premissas e resumo calculado', function () {
    $user = User::factory()->create();
    $investiment = Investiment::factory()->stock()->create([
        'name' => 'PSSA3',
        'value' => 49.51,
        'average_price' => 49.51,
    ]);

    $this->actingAs($user);

    $response = $this->post(route('preco-teto.store'), [
        'investiment_id' => $investiment->id,
        'desired_yield' => '6',
        'projected_payout' => '55',
        'projected_net_income' => 'R$ 3.450.000.000,00',
        'total_shares' => '640.321.918',
        'projected_growth_rate' => '0,00',
        'current_price_per_share' => '49,51',
    ]);

    $valuation = InvestimentValuation::query()
        ->where('investiment_id', $investiment->id)
        ->where('method', InvestimentValuation::METHOD_PRECO_TETO)
        ->first();

    expect($valuation)->not()->toBeNull();
    expect($valuation->assumptions['total_shares'])->toBe(640321918);
    expect($valuation->summary['projected_eps'])->toBe(5.3879);
    expect($valuation->summary['projected_dps'])->toBe(2.9634);
    expect($valuation->summary['fair_value_per_share'])->toBe(49.39);
    expect($valuation->summary['margin_of_safety'])->toBe(-0.24);

    $response->assertRedirect(route('valuations.show', $valuation));
});

it('atualiza uma simulação existente de preço teto projetivo', function () {
    $user = User::factory()->create();
    $investiment = Investiment::factory()->stock()->create();
    $valuation = InvestimentValuation::factory()->create([
        'investiment_id' => $investiment->id,
        'method' => InvestimentValuation::METHOD_PRECO_TETO,
    ]);

    $this->actingAs($user);

    $response = $this->put(route('preco-teto.update', $valuation), [
        'investiment_id' => $investiment->id,
        'desired_yield' => '6',
        'projected_payout' => '55',
        'projected_net_income' => '3450000000',
        'total_shares' => '640.321.918',
        'projected_growth_rate' => '0',
        'current_price_per_share' => '49.51',
    ]);

    $valuation->refresh();

    expect($valuation->method)->toBe(InvestimentValuation::METHOD_PRECO_TETO);
    expect($valuation->summary['price_ceiling'])->toBe(49.39);

    $response->assertRedirect(route('valuations.show', $valuation));
});

it('lista um ativo com as margens dos dois métodos de valuation', function () {
    $user = User::factory()->create();
    $investiment = Investiment::factory()->stock()->create(['name' => 'PSSA3']);

    InvestimentValuation::factory()->create([
        'investiment_id' => $investiment->id,
        'method' => InvestimentValuation::METHOD_DCF,
        'summary' => [
            'fair_value_per_share' => 60,
            'margin_of_safety' => 20,
            'upside' => 25,
        ],
        'calculated_at' => now()->subDay(),
    ]);

    InvestimentValuation::factory()->create([
        'investiment_id' => $investiment->id,
        'method' => InvestimentValuation::METHOD_PRECO_TETO,
        'summary' => [
            'fair_value_per_share' => 49.39,
            'price_ceiling' => 49.39,
            'margin_of_safety' => -0.24,
            'upside' => -0.24,
        ],
        'calculated_at' => now(),
    ]);

    $this->actingAs($user);

    $response = $this->get(route('valuations.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('valuations/Index')
        ->where('valuations.data.0.investiment.name', 'PSSA3')
        ->where('valuations.data.0.dcf.margin_of_safety', 20)
        ->where('valuations.data.0.preco_teto.margin_of_safety', -0.24)
    );
});
