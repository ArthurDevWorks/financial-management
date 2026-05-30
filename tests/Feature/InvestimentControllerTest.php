<?php

use App\Enums\CategoryType;
use App\Models\Category;
use App\Models\Investiment;
use App\Models\InvestimentValuation;
use App\Models\User;

it('carrega apenas categorias permitidas no formulário de criação', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Category::factory()->create(['type' => CategoryType::INVESTMENT->value, 'name' => 'Acoes']);
    Category::factory()->create(['type' => CategoryType::INVESTMENT->value, 'name' => 'ETF']);
    Category::factory()->create(['type' => CategoryType::INVESTMENT->value, 'name' => 'Outro']);
    Category::factory()->create(['type' => CategoryType::REVENUE->value, 'name' => 'Receita']);

    $response = $this->get(route('investiments.create'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('investiments/Create')
        ->has('categories', 2)
        ->where('categories.0.name', 'Acoes')
        ->where('categories.1.name', 'ETF')
    );
});

it('cadastra investimento e usa defaults corretos na visualização', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['type' => CategoryType::INVESTMENT->value, 'name' => 'ETF']);

    $this->actingAs($user);

    $response = $this->post(route('investiments.store'), [
        'name' => 'ETF Brasil',
        'value' => '100.50',
        'type' => $category->id,
    ]);

    $response->assertRedirect(route('investiments.index'));
    $response->assertSessionHas('success', 'Investimento cadastrado com sucesso');

    $investiment = Investiment::query()->latest('id')->first();

    expect($investiment)->not()->toBeNull();
    expect((float) $investiment->value)->toBe(100.50);

    $this->get(route('investiments.show', $investiment))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('investiments/Valuation')
            ->where('investiment.id', $investiment->id)
            ->where('defaultAssumptions.current_price_per_share', '100.50')
            ->where('defaultAssumptions.discount_rate', '12')
            ->where('defaultAssumptions.growth_rates.0', '6')
        );
});

it('executa valuation e persiste histórico', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['type' => CategoryType::INVESTMENT->value, 'name' => 'ETF']);
    $investiment = Investiment::factory()->create(['type' => $category->id, 'value' => 100]);

    $this->actingAs($user);

    $payload = [
        'current_fcf' => '100',
        'total_shares' => '10',
        'current_price_per_share' => '120',
        'payout' => '50',
        'roe' => '20',
        'discount_rate' => '10',
        'terminal_growth_rate' => '3',
        'projection_years' => '3',
        'growth_rates' => ['10', '10', '10'],
    ];

    $response = $this->post(route('investiments.valuation', $investiment), $payload);

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('investiments/Valuation')
        ->where('investiment.id', $investiment->id)
        ->where('defaultAssumptions.current_fcf', '100')
        ->where('defaultAssumptions.growth_rates.0', '10')
    );

    $this->assertDatabaseCount('investiment_valuations', 1);

    $valuation = InvestimentValuation::query()->first();

    expect($valuation)->not()->toBeNull();
    expect($valuation->investiment_id)->toBe($investiment->id);
    expect($valuation->assumptions['current_fcf'])->toBe(100);
});
