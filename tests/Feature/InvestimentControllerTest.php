<?php

use App\Enums\FixedIncomeIndexer;
use App\Enums\FixedIncomeProfitabilityType;
use App\Enums\InvestmentAssetType;
use App\Models\Investiment;
use App\Models\InvestimentValuation;
use App\Models\User;

it('carrega tipos de ativos e opções de renda fixa no formulário de criação', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('investiments.create'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('investiments/Create')
        ->has('assetTypes', 11)
        ->where('assetTypes.0.value', InvestmentAssetType::STOCK->value)
        ->where('assetTypes.0.label', 'Ações')
        ->has('fixedIncomeProfitabilityTypes', 3)
        ->has('fixedIncomeIndexers', 5)
    );
});

it('cadastra investimento com quantidade preço médio saldo e usa defaults corretos na visualização', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->post(route('investiments.store'), [
        'name' => 'PETR4',
        'type' => InvestmentAssetType::STOCK->value,
        'quantity' => '100',
        'average_price' => '32.50',
        'current_balance' => '3800',
    ]);

    $response->assertRedirect(route('investiments.index'));
    $response->assertSessionHas('success', 'Investimento cadastrado com sucesso');

    $investiment = Investiment::query()->latest('id')->first();

    expect($investiment)->not()->toBeNull();
    expect($investiment->type)->toBe(InvestmentAssetType::STOCK);
    expect((float) $investiment->quantity)->toBe(100.0);
    expect((float) $investiment->average_price)->toBe(32.50);
    expect((float) $investiment->current_balance)->toBe(3800.0);
    expect((float) $investiment->profitability)->toBe(16.92);

    $this->get(route('investiments.show', $investiment))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('investiments/Valuation')
            ->where('investiment.id', $investiment->id)
            ->where('investiment.type_label', 'Ações')
            ->where('defaultAssumptions.current_price_per_share', '32.50')
            ->where('defaultAssumptions.discount_rate', '12')
            ->where('defaultAssumptions.growth_rates.0', '6')
        );
});

it('cadastra renda fixa com rentabilidade indexador taxa vencimento e liquidez', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->post(route('investiments.store'), [
        'name' => 'CDB Banco XPTO',
        'type' => InvestmentAssetType::CDB->value,
        'quantity' => '1',
        'average_price' => '1000',
        'current_balance' => '1045.50',
        'profitability_type' => FixedIncomeProfitabilityType::POST_FIXED->value,
        'indexer' => FixedIncomeIndexer::CDI->value,
        'contracted_rate' => '110',
        'maturity_date' => now()->addYear()->toDateString(),
        'liquidity' => 'D+1',
    ]);

    $response->assertRedirect(route('investiments.index'));

    $investiment = Investiment::query()->latest('id')->first();

    expect($investiment)->not()->toBeNull();
    expect($investiment->type)->toBe(InvestmentAssetType::CDB);
    expect($investiment->profitability_type)->toBe(FixedIncomeProfitabilityType::POST_FIXED);
    expect($investiment->indexer)->toBe(FixedIncomeIndexer::CDI);
    expect((float) $investiment->contracted_rate)->toBe(110.0);
    expect($investiment->liquidity)->toBe('D+1');
});

it('exibe resumo consolidado da carteira na listagem', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Investiment::factory()->create([
        'name' => 'PETR4',
        'type' => InvestmentAssetType::STOCK->value,
        'quantity' => 100,
        'average_price' => 32.50,
        'current_balance' => 3800,
        'value' => 32.50,
        'profitability' => 16.92,
    ]);

    $response = $this->get(route('investiments.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('investiments/Index')
        ->where('portfolioSummary.totalInvested', 3250)
        ->where('portfolioSummary.currentBalance', 3800)
        ->where('portfolioSummary.totalGainLoss', 550)
        ->where('portfolioSummary.totalProfitability', 16.92)
        ->where('portfolioSummary.distributionByClass.0.class', 'Ações')
        ->where('portfolioSummary.distributionByClass.0.total', 3800)
        ->where('investiments.data.0.type_label', 'Ações')
        ->where('investiments.data.0.quantity', 100)
    );
});

it('executa valuation e persiste histórico', function () {
    $user = User::factory()->create();
    $investiment = Investiment::factory()->create([
        'type' => InvestmentAssetType::ETF->value,
        'quantity' => 1,
        'average_price' => 100,
        'current_balance' => 100,
        'value' => 100,
        'profitability' => 0,
    ]);

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
        ->where('investiment.type_label', 'ETFs')
        ->where('defaultAssumptions.current_fcf', '100')
        ->where('defaultAssumptions.growth_rates.0', '10')
    );

    $this->assertDatabaseCount('investiment_valuations', 1);

    $valuation = InvestimentValuation::query()->first();

    expect($valuation)->not()->toBeNull();
    expect($valuation->investiment_id)->toBe($investiment->id);
    expect($valuation->assumptions['current_fcf'])->toBe(100);
});
