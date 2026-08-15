<?php

use App\Models\Asset;
use App\Models\InvestimentValuation;
use App\Models\User;

function dcfPayload(array $overrides = []): array
{
    return array_merge([
        'asset_id' => 1,
        'current_fcf' => 'R$ 1.500.000.000,00',
        'roe' => '22,5',
        'payout' => '50',
        'discount_rate' => '12,5',
        'terminal_growth_rate' => '3',
        'projection_years' => '5',
        'total_shares' => '100.000.000',
        'current_price_per_share' => '49,51',
        'growth_rates' => ['8', '7', '6', '5', '4'],
    ], $overrides);
}

it('salva uma simulação DCF normalizando entradas numéricas', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->stock()->create();

    $this->actingAs($user);

    $response = $this->post(route('dcf.store'), dcfPayload([
        'asset_id' => $asset->id,
    ]));

    $valuation = InvestimentValuation::query()
        ->where('user_id', $user->id)
        ->where('asset_id', $asset->id)
        ->where('method', InvestimentValuation::METHOD_DCF)
        ->first();

    expect($valuation)->not()->toBeNull();
    expect($valuation->assumptions['current_fcf'])->toBe('1500000000.00');
    expect($valuation->assumptions['roe'])->toBe('22.5');
    expect($valuation->assumptions['discount_rate'])->toBe('12.5');
    expect($valuation->assumptions['total_shares'])->toBe('100000000');
    expect($valuation->assumptions['growth_rates'])->toBe(['8', '7', '6', '5', '4']);

    $response->assertRedirect();
    $response->assertSessionHas('success', 'Valuation DCF salva com sucesso');
});

it('reutiliza a valuation DCF existente do mesmo ativo no store', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->stock()->create();

    $existing = InvestimentValuation::factory()->for($user)->create([
        'asset_id' => $asset->id,
        'method' => InvestimentValuation::METHOD_DCF,
    ]);

    $this->actingAs($user);

    $this->post(route('dcf.store'), dcfPayload([
        'asset_id' => $asset->id,
        'current_fcf' => '2000000000',
    ]));

    $existing->refresh();

    expect($existing->assumptions['current_fcf'])->toBe('2000000000');
    expect(InvestimentValuation::where('asset_id', $asset->id)->where('method', InvestimentValuation::METHOD_DCF)->count())->toBe(1);
});

it('atualiza uma valuation DCF própria', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->stock()->create();

    $valuation = InvestimentValuation::factory()->for($user)->create([
        'asset_id' => $asset->id,
        'method' => InvestimentValuation::METHOD_DCF,
    ]);

    $this->actingAs($user);

    $response = $this->put(route('dcf.update', $valuation), dcfPayload([
        'asset_id' => $asset->id,
        'discount_rate' => '11',
        'terminal_growth_rate' => '2',
    ]));

    $valuation->refresh();

    expect($valuation->assumptions['discount_rate'])->toBe('11');
    expect($valuation->assumptions['terminal_growth_rate'])->toBe('2');

    $response->assertRedirect();
    $response->assertSessionHas('success', 'Valuation DCF atualizada com sucesso');
});

it('bloqueia atualização de valuation DCF de outro usuário', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $asset = Asset::factory()->stock()->create();

    $valuation = InvestimentValuation::factory()->for($otherUser)->create([
        'asset_id' => $asset->id,
        'method' => InvestimentValuation::METHOD_DCF,
    ]);

    $this->actingAs($user);

    $this->put(route('dcf.update', $valuation), dcfPayload([
        'asset_id' => $asset->id,
    ]))->assertForbidden();

    $valuation->refresh();
    expect($valuation->assumptions['current_fcf'])->toBe(100);
});

it('rejeita atualização de valuation de outro método na rota DCF', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->stock()->create();

    $valuation = InvestimentValuation::factory()->for($user)->create([
        'asset_id' => $asset->id,
        'method' => InvestimentValuation::METHOD_GORDON,
    ]);

    $this->actingAs($user);

    $this->put(route('dcf.update', $valuation), dcfPayload([
        'asset_id' => $asset->id,
    ]))->assertNotFound();
});

it('carrega a página DCF com a valuation própria via query string', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->stock()->create();

    $valuation = InvestimentValuation::factory()->for($user)->create([
        'asset_id' => $asset->id,
        'method' => InvestimentValuation::METHOD_DCF,
        'assumptions' => [
            'current_fcf' => 1000,
            'roe' => 20,
            'payout' => 60,
            'discount_rate' => 12,
            'terminal_growth_rate' => 3,
            'projection_years' => 5,
            'total_shares' => 100,
            'current_price_per_share' => 10,
            'growth_rates' => [8, 7, 6, 5, 4],
        ],
    ]);

    $this->actingAs($user);

    $response = $this->get(route('dcf.index', ['valuation_id' => $valuation->id]));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('dcf/Index')
        ->where('valuation.id', $valuation->id)
        ->where('asset.ticker', $asset->ticker)
        ->where('defaultAssumptions.current_fcf', '1000')
    );
});

it('retorna 404 ao abrir valuation de outro usuário na página DCF', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $asset = Asset::factory()->stock()->create();

    $valuation = InvestimentValuation::factory()->for($otherUser)->create([
        'asset_id' => $asset->id,
        'method' => InvestimentValuation::METHOD_DCF,
    ]);

    $this->actingAs($user);

    $this->get(route('dcf.index', ['valuation_id' => $valuation->id]))->assertNotFound();
});

it('valida premissas obrigatórias do DCF', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->stock()->create();

    $this->actingAs($user);

    $this->post(route('dcf.store'), [
        'asset_id' => $asset->id,
        'current_fcf' => '100',
        'total_shares' => '10',
        'payout' => '150',
        'roe' => '20',
        'discount_rate' => '12',
        'terminal_growth_rate' => '15',
        'projection_years' => '5',
        'growth_rates' => ['8', '7', '6', '5'],
    ])->assertSessionHasErrors(['payout', 'terminal_growth_rate']);

    $this->assertDatabaseCount('investiment_valuations', 0);
});
