<?php

use App\Enums\InvestmentAssetType;
use App\Models\Investiment;
use App\Models\InvestimentValuation;
use App\Models\User;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    Http::preventStrayRequests();
});

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
    Http::fake([
        'brapi.dev/*' => Http::response([
            'results' => [
                [
                    'symbol' => 'PETR4',
                    'data' => [
                        'regularMarketPrice' => 3800.0,
                        'logourl' => 'https://icons.brapi.dev/icons/PETR4.svg',
                        'regularMarketChange' => 0.0,
                        'regularMarketChangePercent' => 0.0,
                        'marketCap' => 500000000000,
                        'regularMarketVolume' => 1000000,
                    ],
                ],
            ],
        ]),
    ]);

    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->post(route('investiments.store'), [
        'name' => 'PETR4',
        'type' => InvestmentAssetType::STOCK->value,
        'current_balance' => 3800.00,
    ]);

    $response->assertRedirect(route('investiments.index'));
    $response->assertSessionHas('success', 'Investimento cadastrado com sucesso');

    $investiment = Investiment::query()->latest('id')->first();

    expect($investiment)->not()->toBeNull();
    expect($investiment->type)->toBe(InvestmentAssetType::STOCK);
    expect((float) $investiment->current_balance)->toBe(3800.0);
});

it('exibe a listagem de investimentos com valor da cotação', function () {
    Http::fake([
        'brapi.dev/*' => Http::response([
            'results' => [
                [
                    'symbol' => 'PETR4',
                    'data' => [
                        'regularMarketPrice' => 3800.0,
                        'logourl' => 'https://icons.brapi.dev/icons/PETR4.svg',
                        'regularMarketChange' => 0.0,
                        'regularMarketChangePercent' => 0.0,
                        'marketCap' => 500000000000,
                        'regularMarketVolume' => 1000000,
                    ],
                ],
            ],
        ]),
    ]);

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

it('usa a ultima taxa projetada como crescimento na perpetuidade no valuation dcf', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $investiment = Investiment::factory()->stock()->create([
        'name' => 'BBSE3',
        'value' => 38.67,
        'average_price' => 38.67,
        'current_balance' => 38.67,
    ]);

    $response = $this->post(route('investiments.valuation', $investiment), [
        'current_fcf' => 8400000000,
        'discount_rate' => 15,
        'terminal_growth_rate' => 3,
        'projection_years' => 3,
        'total_shares' => 1941400000,
        'payout' => 85,
        'roe' => 66,
        'current_price_per_share' => 38.67,
        'growth_rates' => [6, 6, 6],
    ]);

    $response->assertOk();

    $valuation = $investiment->valuations()->latest('id')->first();

    expect($valuation)->toBeInstanceOf(InvestimentValuation::class);
    expect($valuation->assumptions['terminal_growth_rate'])->toBe(6);
    expect($valuation->summary['terminal_value'])->toBe(117831182933.33);
    expect($valuation->summary['fair_value_per_share'])->toBe(50.96);
    expect($valuation->summary['upside'])->toBe(31.78);
});
