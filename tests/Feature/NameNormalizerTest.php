<?php

use App\Services\NameNormalizer;

test('normalize maps itsa3 to itausa name', function () {
    $result = NameNormalizer::normalize('ITSA3', 'Itaúsa');

    expect($result)->toBe('ITAÚSA INVESTIMENTOS ITAÚ SA');
});

test('normalize maps itsa4 to same itausa name', function () {
    $result = NameNormalizer::normalize('ITSA4', 'Itaúsa Investimentos');

    expect($result)->toBe('ITAÚSA INVESTIMENTOS ITAÚ SA');
});

test('normalize maps itub3 to itau unibanco', function () {
    $result = NameNormalizer::normalize('ITUB3', 'Itaú Unibanco');

    expect($result)->toBe('ITAÚ UNIBANCO HOLDING SA');
});

test('normalize maps itub4 to same itau name', function () {
    $result = NameNormalizer::normalize('ITUB4', 'Itaú Unibanco Holding');

    expect($result)->toBe('ITAÚ UNIBANCO HOLDING SA');
});

test('normalize maps petr3 to petrobras pn', function () {
    $result = NameNormalizer::normalize('PETR3', 'Petrobras');

    expect($result)->toBe('PETROBRAS PN');
});

test('normalize maps petr4 to petrobras on', function () {
    $result = NameNormalizer::normalize('PETR4', 'Petrobras');

    expect($result)->toBe('PETROBRAS ON');
});

test('normalize maps petz3 to pet center', function () {
    $result = NameNormalizer::normalize('PETZ3', 'Petz');

    expect($result)->toBe('PET CENTER COMÉRCIO E PARTICIPAÇÕES SA');
});

test('normalize maps vale3 to vale on', function () {
    $result = NameNormalizer::normalize('VALE3', 'Vale');

    expect($result)->toBe('VALE ON');
});

test('normalize maps vale5 to vale pn', function () {
    $result = NameNormalizer::normalize('VALE5', 'Vale PN');

    expect($result)->toBe('VALE PN');
});

test('normalize maps bbas3 to banco do brasil on', function () {
    $result = NameNormalizer::normalize('BBAS3', 'Banco do Brasil');

    expect($result)->toBe('BANCO DO BRASIL ON');
});

test('normalize maps bbdc3 to bradesco pn', function () {
    $result = NameNormalizer::normalize('BBDC3', 'Bradesco');

    expect($result)->toBe('BANCO BRADESCO PN');
});

test('normalize maps bbdc4 to bradesco on', function () {
    $result = NameNormalizer::normalize('BBDC4', 'Bradesco');

    expect($result)->toBe('BANCO BRADESCO ON');
});

test('normalize maps abev3 to ambev on', function () {
    $result = NameNormalizer::normalize('ABEV3', 'Ambev');

    expect($result)->toBe('AMBEV ON');
});

test('normalize maps b3sa3 to b3 on', function () {
    $result = NameNormalizer::normalize('B3SA3', 'B3');

    expect($result)->toBe('B3 ON');
});

test('normalize maps wege3 to weg on', function () {
    $result = NameNormalizer::normalize('WEGE3', 'WEG');

    expect($result)->toBe('WEG ON');
});

test('normalize maps suzb3 to suzano on', function () {
    $result = NameNormalizer::normalize('SUZB3', 'Suzano');

    expect($result)->toBe('SUZANO ON');
});

test('normalize maps jbs3 to jbs on', function () {
    $result = NameNormalizer::normalize('JBSS3', 'JBS');

    expect($result)->toBe('JBS ON');
});

test('normalize maps rent3 to localiza on', function () {
    $result = NameNormalizer::normalize('RENT3', 'Localiza');

    expect($result)->toBe('LOCALIZA ON');
});

test('normalize maps mglu3 to magazine luiza on', function () {
    $result = NameNormalizer::normalize('MGLU3', 'Magazine Luiza');

    expect($result)->toBe('MAGAZINE LUIZA ON');
});

test('normalize maps ciel3 to cielo on', function () {
    $result = NameNormalizer::normalize('CIEL3', 'Cielo');

    expect($result)->toBe('CIELO ON');
});

test('normalize maps embr3 to embracer on', function () {
    $result = NameNormalizer::normalize('EMBR3', 'Embraer');

    expect($result)->toBe('EMBRAER ON');
});

test('normalize maps ggbr4 to gerdau pn', function () {
    $result = NameNormalizer::normalize('GGBR4', 'Gerdau');

    expect($result)->toBe('GERDAU PN');
});

test('normalize maps tots3 to totvs on', function () {
    $result = NameNormalizer::normalize('TOTS3', 'TOTVS');

    expect($result)->toBe('TOTVS ON');
});

test('normalize maps flry3 to fleury on', function () {
    $result = NameNormalizer::normalize('FLRY3', 'Fleury');

    expect($result)->toBe('FLEURY ON');
});

test('normalize maps hype3 to hypera on', function () {
    $result = NameNormalizer::normalize('HYPE3', 'Hypera');

    expect($result)->toBe('HYPERA ON');
});

test('normalize maps prio3 to prio on', function () {
    $result = NameNormalizer::normalize('PRIO3', 'PRIO');

    expect($result)->toBe('PRIO ON');
});

test('normalize maps rail3 to rumo on', function () {
    $result = NameNormalizer::normalize('RAIL3', 'Rumo');

    expect($result)->toBe('RUMO ON');
});

test('normalize maps csmg3 to copasa on', function () {
    $result = NameNormalizer::normalize('CSMG3', 'Copasa');

    expect($result)->toBe('COPASA ON');
});

test('normalize maps sbsp3 to sabesp on', function () {
    $result = NameNormalizer::normalize('SBSP3', 'Sabesp');

    expect($result)->toBe('SABESP ON');
});

test('normalize maps lren3 to renner on', function () {
    $result = NameNormalizer::normalize('LREN3', 'Renner');

    expect($result)->toBe('RENNER ON');
});

test('normalize maps arzz3 to arezzo on', function () {
    $result = NameNormalizer::normalize('ARZZ3', 'Arezzo');

    expect($result)->toBe('AREZZO ON');
});

test('normalize maps yduq3 to yduqs on', function () {
    $result = NameNormalizer::normalize('YDUQ3', 'Yduqs');

    expect($result)->toBe('YDUQS ON');
});

test('normalize maps hapv3 to hapvida on', function () {
    $result = NameNormalizer::normalize('HAPV3', 'Hapvida');

    expect($result)->toBe('HAPVIDA ON');
});

test('normalize maps rdor3 to rede dor on', function () {
    $result = NameNormalizer::normalize('RDOR3', 'Rede D\'Or');

    expect($result)->toBe('REDE D\'OR ON');
});

test('normalize maps klbn3 to klabin on', function () {
    $result = NameNormalizer::normalize('KLBN3', 'Klabin');

    expect($result)->toBe('KLABIN ON');
});

test('normalize maps csan3 to cosan on', function () {
    $result = NameNormalizer::normalize('CSAN3', 'Cosan');

    expect($result)->toBe('COSAN ON');
});

test('normalize maps pags3 to pagseguro digital on', function () {
    $result = NameNormalizer::normalize('PAGS3', 'PagSeguro Digital');

    expect($result)->toBe('PAGSEGURO DIGITAL ON');
});

test('normalize maps stne3 to stone co on', function () {
    $result = NameNormalizer::normalize('STNE3', 'Stone Co');

    expect($result)->toBe('STONE CO ON');
});

test('normalize maps posit3 to positivo on', function () {
    $result = NameNormalizer::normalize('POSI3', 'Positivo Tecnologia');

    expect($result)->toBe('POSITIVO TECNOLOGIA ON');
});

test('normalize maps brfs3 to brf on', function () {
    $result = NameNormalizer::normalize('BRFS3', 'BRF');

    expect($result)->toBe('BRF ON');
});

test('normalize maps bbse3 to bb seguridade on', function () {
    $result = NameNormalizer::normalize('BBSE3', 'BB Seguridade');

    expect($result)->toBe('BB SEGURIDADE ON');
});

test('normalize handles unknown ticker with raw name', function () {
    $result = NameNormalizer::normalize('ABCD3', 'Some Company SA');

    expect($result)->toBe('SOME COMPANY SA');
});

test('normalize handles null raw name for unknown ticker', function () {
    $result = NameNormalizer::normalize('ABCD3', null);

    expect($result)->toBeNull();
});

test('normalize cleans sa suffix from name', function () {
    $result = NameNormalizer::normalize('TEST3', 'Test Company S.A.');

    expect($result)->toBe('TEST COMPANY SA');
});

test('normalize cleans pn suffix from name', function () {
    $result = NameNormalizer::normalize('TEST3', 'Test Company P.N');

    expect($result)->toBe('TEST COMPANY PN');
});

test('normalize cleans on suffix from name', function () {
    $result = NameNormalizer::normalize('TEST3', 'Test Company O.N');

    expect($result)->toBe('TEST COMPANY ON');
});

test('normalize handles ticker case insensitivity', function () {
    $result = NameNormalizer::normalize('itsa3', 'Itaúsa');

    expect($result)->toBe('ITAÚSA INVESTIMENTOS ITAÚ SA');
});

test('normalize handles multiple spaces in name', function () {
    $result = NameNormalizer::normalize('TEST3', 'Test   Company   SA');

    expect($result)->toBe('TEST COMPANY SA');
});
