<?php

use App\Services\DcfValuationService;

it('calcula valuation e preenche taxas de crescimento em falta', function () {
    $service = new DcfValuationService;

    $resultado = $service->calculate([
        'current_fcf' => 100,
        'payout' => 50,
        'roe' => 20,
        'discount_rate' => 10,
        'terminal_growth_rate' => 3,
        'projection_years' => 3,
        'total_shares' => 10,
        'current_price_per_share' => 12,
        'growth_rates' => [10],
    ]);

    expect($resultado['assumptions']['growth_rates'])->toBe([10.0, 10.0, 10.0]);
    expect($resultado['assumptions']['base_growth_rate'])->toBe(10.0);
    expect($resultado['projected_cash_flows'])->toHaveCount(3);
    expect($resultado['projected_cash_flows'][0]['projected_fcf'])->toBe(110.0);
    expect($resultado['projected_cash_flows'][1]['projected_fcf'])->toBe(121.0);
    expect($resultado['summary']['market_cap'])->toBe(120.0);
    expect($resultado['assumptions']['current_price_per_share'])->toBe(12.0);
});

it('retorna campos nulos quando o preço atual da ação não é informado', function () {
    $service = new DcfValuationService;

    $resultado = $service->calculate([
        'current_fcf' => 100,
        'payout' => 50,
        'roe' => 20,
        'discount_rate' => 10,
        'terminal_growth_rate' => 3,
        'projection_years' => 3,
        'total_shares' => 10,
        'current_price_per_share' => null,
        'growth_rates' => [10, 10, 10],
    ]);

    expect($resultado['summary']['market_cap'])->toBeNull();
    expect($resultado['summary']['upside'])->toBeNull();
    expect($resultado['summary']['margin_of_safety'])->toBeNull();
});

it('não divide por zero quando o valor justo por ação zera', function () {
    $service = new DcfValuationService;

    $resultado = $service->calculate([
        'current_fcf' => 0,
        'payout' => 50,
        'roe' => 20,
        'discount_rate' => 10,
        'terminal_growth_rate' => 3,
        'projection_years' => 3,
        'total_shares' => 10,
        'current_price_per_share' => 12,
        'growth_rates' => [0, 0, 0],
    ]);

    expect($resultado['summary']['fair_value_per_share'])->toBe(0.0);
    expect($resultado['summary']['upside'])->toBe(-100.0);
    expect($resultado['summary']['margin_of_safety'])->toBeNull();
});
