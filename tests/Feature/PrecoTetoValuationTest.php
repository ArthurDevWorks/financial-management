<?php

use App\Models\Asset;
use App\Models\InvestimentValuation;
use App\Models\User;

it('salva a simulação de preço teto projetivo com premissas', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->stock()->create([
        'ticker' => 'PSSA3',
        'name' => 'PSSA3',
    ]);

    $this->actingAs($user);

    $response = $this->post(route('preco-teto.store'), [
        'asset_id' => $asset->id,
        'desired_yield' => '6',
        'projected_payout' => '55',
        'projected_net_income' => 'R$ 3.450.000.000,00',
        'total_shares' => '640.321.918',
        'projected_growth_rate' => '0,00',
        'current_price_per_share' => '49,51',
    ]);

    $valuation = InvestimentValuation::query()
        ->where('asset_id', $asset->id)
        ->where('method', InvestimentValuation::METHOD_PRECO_TETO)
        ->first();

    expect($valuation)->not()->toBeNull();
    expect($valuation->assumptions['desired_yield'])->toBe('6');
    expect($valuation->assumptions['projected_payout'])->toBe('55');
    expect($valuation->assumptions['total_shares'])->toBe('640321918');
    expect($valuation->assumptions['current_price_per_share'])->toBe('49.51');

    $response->assertRedirect(route('valuations.index'));
});

it('atualiza uma simulação existente de preço teto projetivo', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->stock()->create();
    $valuation = InvestimentValuation::factory()->create([
        'asset_id' => $asset->id,
        'method' => InvestimentValuation::METHOD_PRECO_TETO,
    ]);

    $this->actingAs($user);

    $response = $this->put(route('preco-teto.update', $valuation), [
        'asset_id' => $asset->id,
        'desired_yield' => '6',
        'projected_payout' => '55',
        'projected_net_income' => '3450000000',
        'total_shares' => '640.321.918',
        'projected_growth_rate' => '0',
        'current_price_per_share' => '49.51',
    ]);

    $valuation->refresh();

    expect($valuation->method)->toBe(InvestimentValuation::METHOD_PRECO_TETO);
    expect($valuation->assumptions['desired_yield'])->toBe('6');
    expect($valuation->assumptions['current_price_per_share'])->toBe('49.51');

    $response->assertRedirect(route('valuations.show', $valuation));
});

it('lista valuations agrupadas por ativo na rota index', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->stock()->create(['name' => 'PSSA3']);

    InvestimentValuation::factory()->create([
        'asset_id' => $asset->id,
        'method' => InvestimentValuation::METHOD_DCF,
        'calculated_at' => now()->subDay(),
    ]);

    InvestimentValuation::factory()->create([
        'asset_id' => $asset->id,
        'method' => InvestimentValuation::METHOD_PRECO_TETO,
        'calculated_at' => now(),
    ]);

    $this->actingAs($user);

    $response = $this->get(route('valuations.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('valuations/Index')
        ->has('valuations.data', 1)
        ->where('valuations.data.0.name', 'PSSA3')
        ->where('valuations.data.0.valuation_count', 2)
        ->has('valuations.data.0.valuations', 2)
        ->where('valuations.data.0.valuations.0.method', InvestimentValuation::METHOD_PRECO_TETO)
        ->where('valuations.data.0.valuations.1.method', InvestimentValuation::METHOD_DCF)
    );
});
