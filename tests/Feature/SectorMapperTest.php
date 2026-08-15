<?php

use App\Services\SectorMapper;

test('normalize maps brapi energy sector to energ', function () {
    $result = SectorMapper::normalize('Energy', 'Electric Utilities');

    expect($result['sector'])->toBe('Energia');
    expect($result['subsector'])->toBe('Energia Elétrica');
});

test('normalize maps brapi financial services to financeiro', function () {
    $result = SectorMapper::normalize('Financial Services', 'Banks—Regional');

    expect($result['sector'])->toBe('Financeiro');
    expect($result['subsector'])->toBe('Bancos');
});

test('normalize maps brapi technology sector to tecnologia', function () {
    $result = SectorMapper::normalize('Technology', 'Software—Application');

    expect($result['sector'])->toBe('Tecnologia');
    expect($result['subsector'])->toBe('Software');
});

test('normalize maps brapi oil and gas industry to petroleo e gas', function () {
    $result = SectorMapper::normalize('Energy', 'Oil & Gas Integrated');

    expect($result['sector'])->toBe('Energia');
    expect($result['subsector'])->toBe('Petróleo e Gás');
});

test('normalize maps brapi solar industry to energias renovaveis', function () {
    $result = SectorMapper::normalize('Energy', 'Solar');

    expect($result['sector'])->toBe('Energia');
    expect($result['subsector'])->toBe('Energias Renováveis');
});

test('normalize maps brapi banks diversified to bancos', function () {
    $result = SectorMapper::normalize('Financial Services', 'Banks—Diversified');

    expect($result['sector'])->toBe('Financeiro');
    expect($result['subsector'])->toBe('Bancos');
});

test('normalize maps brapi reit office to lajes', function () {
    $result = SectorMapper::normalize('Real Estate', 'REIT—Office');

    expect($result['sector'])->toBe('Imobiliário');
    expect($result['subsector'])->toBe('Lajes');
});

test('normalize maps brapi reit retail to shoppings', function () {
    $result = SectorMapper::normalize('Real Estate', 'REIT—Retail');

    expect($result['sector'])->toBe('Imobiliário');
    expect($result['subsector'])->toBe('Shoppings');
});

test('normalize maps brapi reit industrial to logistica', function () {
    $result = SectorMapper::normalize('Real Estate', 'REIT—Industrial');

    expect($result['sector'])->toBe('Imobiliário');
    expect($result['subsector'])->toBe('Logística');
});

test('normalize maps brapi drug manufacturers to farmaceutica', function () {
    $result = SectorMapper::normalize('Healthcare', 'Drug Manufacturers—General');

    expect($result['sector'])->toBe('Saúde');
    expect($result['subsector'])->toBe('Farmacêutica');
});

test('normalize maps brapi gold mining to mineracao', function () {
    $result = SectorMapper::normalize('Basic Materials', 'Gold');

    expect($result['sector'])->toBe('Materiais Básicos');
    expect($result['subsector'])->toBe('Mineração');
});

test('normalize maps brapi steel to siderurgia', function () {
    $result = SectorMapper::normalize('Basic Materials', 'Steel');

    expect($result['sector'])->toBe('Materiais Básicos');
    expect($result['subsector'])->toBe('Siderurgia');
});

test('normalize maps brapi engineering and construction to construcao', function () {
    $result = SectorMapper::normalize('Industrials', 'Engineering & Construction');

    expect($result['sector'])->toBe('Indústria');
    expect($result['subsector'])->toBe('Construção');
});

test('normalize maps brapi auto manufacturers to automotivo', function () {
    $result = SectorMapper::normalize('Consumer Cyclical', 'Auto Manufacturers');

    expect($result['sector'])->toBe('Consumo Cíclico');
    expect($result['subsector'])->toBe('Automotivo');
});

test('normalize maps brapi restaurants to alimentacao', function () {
    $result = SectorMapper::normalize('Consumer Cyclical', 'Restaurants');

    expect($result['sector'])->toBe('Consumo Cíclico');
    expect($result['subsector'])->toBe('Alimentação');
});

test('normalize maps brapi packaged foods to alimentos', function () {
    $result = SectorMapper::normalize('Consumer Defensive', 'Packaged Foods');

    expect($result['sector'])->toBe('Consumo Não Cíclico');
    expect($result['subsector'])->toBe('Alimentos');
});

test('normalize maps brapi telecom services to telecomunicacoes', function () {
    $result = SectorMapper::normalize('Communication Services', 'Telecom Services');

    expect($result['sector'])->toBe('Comunicação');
    expect($result['subsector'])->toBe('Telecomunicações');
});

test('normalize maps brapi electric utilities to energia eletrica', function () {
    $result = SectorMapper::normalize('Utilities', 'Electric Utilities');

    expect($result['sector'])->toBe('Utilidades Públicas');
    expect($result['subsector'])->toBe('Energia Elétrica');
});

test('normalize maps brapi utilities regulated gas to gas', function () {
    $result = SectorMapper::normalize('Utilities', 'Utilities—Regulated Gas');

    expect($result['sector'])->toBe('Utilidades Públicas');
    expect($result['subsector'])->toBe('Gás');
});

test('normalize maps brapi paper and paper products to papel e celulose', function () {
    $result = SectorMapper::normalize('Basic Materials', 'Paper & Paper Products');

    expect($result['sector'])->toBe('Materiais Básicos');
    expect($result['subsector'])->toBe('Papel e Celulose');
});

test('normalize handles null sector', function () {
    $result = SectorMapper::normalize(null, null);

    expect($result['sector'])->toBeNull();
    expect($result['subsector'])->toBeNull();
});

test('normalize handles unknown sector', function () {
    $result = SectorMapper::normalize('Unknown Sector', 'Unknown Industry');

    expect($result['sector'])->toBe('Unknown Sector');
    expect($result['subsector'])->toBe('Unknown Industry');
});

test('normalize handles industry only', function () {
    $result = SectorMapper::normalize(null, 'Solar');

    expect($result['sector'])->toBeNull();
    expect($result['subsector'])->toBe('Energias Renováveis');
});

test('normalize handles empty strings', function () {
    $result = SectorMapper::normalize('', '');

    expect($result['sector'])->toBeNull();
    expect($result['subsector'])->toBeNull();
});

test('knownSectors returns unique values', function () {
    $sectors = SectorMapper::knownSectors();

    expect($sectors)->toBeArray();
    expect(count($sectors))->toBeGreaterThan(5);
    expect($sectors)->toContain('Energia');
    expect($sectors)->toContain('Financeiro');
    expect($sectors)->toContain('Tecnologia');
});

test('knownSubsectors returns unique values', function () {
    $subsectors = SectorMapper::knownSubsectors();

    expect($subsectors)->toBeArray();
    expect(count($subsectors))->toBeGreaterThan(10);
    expect($subsectors)->toContain('Bancos');
    expect($subsectors)->toContain('Software');
    expect($subsectors)->toContain('Petróleo e Gás');
});
