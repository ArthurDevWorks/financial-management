<?php

use App\Enums\InvestmentAssetType;
use App\Models\Investiment;
use App\Models\User;

it('carrega apenas a categoria Ações no formulário de criação', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('investiments.create'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('investiments/Create')
        ->has('assetTypes', 1)
        ->where('assetTypes.0.value', InvestmentAssetType::STOCK->value)
        ->where('assetTypes.0.label', 'Ações')
    );
});

it('cadastra investimento com nome, categoria e valor da cotação', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->post(route('investiments.store'), [
        'name' => 'PETR4',
        'type' => InvestmentAssetType::STOCK->value,
        'current_balance' => '3.800,00',
    ]);

    $response->assertRedirect(route('investiments.index'));
    $response->assertSessionHas('success', 'Investimento cadastrado com sucesso');

    $investiment = Investiment::query()->latest('id')->first();

    expect($investiment)->not()->toBeNull();
    expect($investiment->type)->toBe(InvestmentAssetType::STOCK);
    expect((float) $investiment->current_balance)->toBe(3800.0);
});

it('exibe a listagem de investimentos com valor da cotação', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Investiment::factory()->create([
        'name' => 'PETR4',
        'type' => InvestmentAssetType::STOCK->value,
        'current_balance' => 3800,
    ]);

    $response = $this->get(route('investiments.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('investiments/Index')
        ->where('investiments.data.0.type_label', 'Ações')
        ->where('investiments.data.0.current_balance', 3800)
    );
});
