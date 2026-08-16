<?php

namespace App\Services;

class NameNormalizer
{
    /**
     * Mapeamento manual de tickers para nomes normalizados (UPPER CASE).
     * Usado para empresas com múltiplas classes de ações ou nomes inconsistentes.
     *
     * @var array<string, string>
     */
    private const TICKER_NAME_MAP = [
        // Itaúsa
        'ITSA3' => 'ITAÚSA INVESTIMENTOS ITAÚ SA',
        'ITSA4' => 'ITAÚSA INVESTIMENTOS ITAÚ SA',

        // Itaú Unibanco
        'ITUB3' => 'ITAÚ UNIBANCO HOLDING SA',
        'ITUB4' => 'ITAÚ UNIBANCO HOLDING SA',

        // Petrobras
        'PETR3' => 'PETROBRAS PN',
        'PETR4' => 'PETROBRAS ON',

        // Petz
        'PETZ3' => 'PET CENTER COMÉRCIO E PARTICIPAÇÕES SA',

        // Vale
        'VALE3' => 'VALE ON',
        'VALE5' => 'VALE PN',

        // Banco do Brasil
        'BBAS3' => 'BANCO DO BRASIL ON',
        'BBSE3' => 'BB SEGURIDADE ON',

        // Bradesco
        'BBDC3' => 'BANCO BRADESCO PN',
        'BBDC4' => 'BANCO BRADESCO ON',
        'BBAR11' => 'BRADESCO (UNIT)',

        // Santander
        'SANB11' => 'SANTANDER BRASIL (UNIT)',
        'SANB3' => 'SANTANDER BRASIL PN',
        'SANB4' => 'SANTANDER BRASIL ON',
        'SANB5' => 'SANTANDER BRASIL PNA',

        // Ambev
        'ABEV3' => 'AMBEV ON',
        'ABEV4' => 'AMBEV PN',

        // JBS
        'JBSS3' => 'JBS ON',
        'JBSS2' => 'JBS PNA',

        // B3
        'B3SA3' => 'B3 ON',
        'B3SA4' => 'B3 PN',

        // WEG
        'WEGE3' => 'WEG ON',
        'WEGE5' => 'WEG PN',

        // Suzano
        'SUZB3' => 'SUZANO ON',
        'SUZB5' => 'SUZANO PNA',

        // Raízen
        'RAIZ4' => 'RAÍZEN ON',

        // Eletrobras
        'ELET3' => 'ELETROBRAS PN',
        'ELET6' => 'ELETROBRAS PNA',
        'ELET5' => 'ELETROBRAS ON',

        // Vibra Energia
        'VIVR3' => 'VIBRA ENERGIA ON',

        // Cosan
        'CSAN3' => 'COSAN ON',
        'CSAN4' => 'COSAN PN',

        // Gerdau
        'GGBR4' => 'GERDAU PN',
        'GGBR3' => 'GERDAU ON',
        'GOAU4' => 'METALÚRGICA GERDAU PN',
        'GOAU3' => 'METALÚRGICA GERDAU ON',

        // Localiza
        'RENT3' => 'LOCALIZA ON',
        'RENT4' => 'LOCALIZA PN',

        // Magazine Luiza
        'MGLU3' => 'MAGAZINE LUIZA ON',

        // Via
        'VIIA3' => 'VIA ON',

        // Lojas Americanas
        'LAME4' => 'LOJAS AMERICANAS PN',
        'LAME3' => 'LOJAS AMERICANAS ON',

        // Alpargatas
        'LASA3' => 'ALPARGATAS ON',
        'LASA4' => 'ALPARGATAS PN',

        // Natura
        'NTCO3' => 'NATURA &CO ON',
        'NTCO4' => 'NATURA &CO PN',

        // Hapvida
        'HAPV3' => 'HAPVIDA ON',

        // Rede D'Or
        'RDOR3' => 'REDE D\'OR ON',

        // Anhanguera
        'HETA4' => 'ANHANGUERA PN',

        // Cielo
        'CIEL3' => 'CIELO ON',
        'CIEL4' => 'CIELO PN',

        // PagSeguro
        'PAGS3' => 'PAGSEGURO DIGITAL ON',
        'PAGS4' => 'PAGSEGURO DIGITAL PN',

        // Stone
        'STNE3' => 'STONE CO ON',
        'STNE4' => 'STONE CO PN',

        // BTG Pactual
        'BPAC11' => 'BTG PACTUAL (UNIT)',
        'BPAC3' => 'BTG PACTUAL PN',
        'BPAC5' => 'BTG PACTUAL ON',

        // XP
        'XPBR31' => 'XP INC ON (BDR)',
        'XPBR41' => 'XP INC PN (BDR)',

        // Mercado Livre
        'MELI34' => 'MERCADO LIBRE (BDR)',

        // TOTVS
        'TOTS3' => 'TOTVS ON',
        'TOTS4' => 'TOTVS PN',

        // Positivo
        'POSI3' => 'POSITIVO TECNOLOGIA ON',

        // Embraer
        'EMBR3' => 'EMBRAER ON',

        // MRV
        'MRVE3' => 'MRV ENGENHARIA ON',
        'MRVG3' => 'MRV ENGENHARIA PNA',

        // Cyrela
        'CYRE3' => 'CYRELA ON',

        // Even
        'EVEN3' => 'EVEN CONVOCAÇÃO ON',

        // Eztec
        'EZTC3' => 'EZTEC ON',

        // JHSF
        'JHSF3' => 'JHSF ON',

        // Allen
        'ALOS3' => 'ALLEN ON',

        // Fleury
        'FLRY3' => 'FLEURY ON',

        // Dasa
        'DALI3' => 'DASA ON',

        // Hypera
        'HYPE3' => 'HYPERA ON',

        // Taesa
        'TAEE11' => 'TAESA (UNIT)',
        'TAEE3' => 'TAESA PN',
        'TAEE4' => 'TAESA ON',

        // CPFL
        'CPLE3' => 'CPFL ENERGIA PN',
        'CPLE5' => 'CPFL ENERGIA ON',
        'CPLE6' => 'CPFL ENERGIA PNA',

        // Engie
        'ENGI3' => 'ENGIE BRASIL PN',
        'ENGI4' => 'ENGIE BRASIL ON',
        'ENGI11' => 'ENGIE BRASIL (UNIT)',

        // Rumo
        'RAIL3' => 'RUMO ON',

        // Wilson Sons
        'SONG3' => 'WILSON SONS ON',
        'SONG5' => 'WILSON SONS ON',
        'SONG6' => 'WILSON SONS PNA',

        // PetroRecôncavo
        'PREV3' => 'PETRORECÔNCAVO ON',

        // PRIO
        'PRIO3' => 'PRIO ON',
        'PRIO4' => 'PRIO PN',

        // 3R Petroleum
        'RRRP3' => '3R PETROLEUM ON',

        // Oi
        'OIBR3' => 'OI PN',
        'OIBR4' => 'OI ON',
        'OIBR1' => 'OI PNA',

        // Light
        'LIGT3' => 'LIGHT ON',

        // CEMIG
        'CMIG3' => 'CEMIG PN',
        'CMIG4' => 'CEMIG ON',
        'CMIG1' => 'CEMIG PNA',

        // Copasa
        'CSMG3' => 'COPASA ON',

        // Sabesp
        'SBSP3' => 'SABESP ON',

        // Sanepar
        'SAPR3' => 'SANEPAR PN',
        'SAPR4' => 'SANEPAR ON',
        'SAPR11' => 'SANEPAR (UNIT)',

        // Aegea
        'AEGE3' => 'AEGEA SANEAMENTO ON',

        // Mapfre
        'PFRM3' => 'MAPFRE ON',

        // Porto Seguro
        'PSSA3' => 'PORTO SEGURO ON',

        // Brasil Warrant
        'BGWH3' => 'BRASIL WARRANT ON',

        // IRB
        'IRBR3' => 'IRB BRASIL ON',

        // Multilaser
        'MLAS3' => 'MULTILASER ON',

        // Loblaw
        'LOWR34' => 'LOBLAW (BDR)',

        // Renner
        'LREN3' => 'RENNER ON',
        'LREN4' => 'RENNER PN',

        // Arezzo
        'ARZZ3' => 'AREZZO ON',

        // Tris
        'TRIS3' => 'TRIS ON',

        // Alpargatas
        'ALPA3' => 'ALPARGATAS ON',
        'ALPA4' => 'ALPARGATAS PN',

        // Duratex
        'DTEX3' => 'DURATEX ON',

        // Tupy
        'TUPY3' => 'TUPY ON',
        'TUPY1' => 'TUPY PNA',

        // Marcopolo
        'POMO3' => 'MARCOPOLO PN',
        'POMO4' => 'MARCOPOLO ON',

        // Linx
        'LINX3' => 'LINX ON',

        // Yduqs
        'YDUQ3' => 'YDUQS ON',

        // Estácio
        'ESTC3' => 'ESTÁCIO ON',

        // Kroton
        'KROT3' => 'KROTON ON',
        'KROT4' => 'KROTON PN',

        // Minerva
        'BEEF3' => 'MINERVA ON',
        'BEEF4' => 'MINERVA PN',

        // Marfrig
        'MRFG3' => 'MARFRIG ON',
        'MRFG4' => 'MARFRIG PN',

        // BRF
        'BRFS3' => 'BRF ON',
        'BRFS4' => 'BRF PN',

        // BrasilAgro
        'CRAV3' => 'BRASILAGRO ON',

        // Adecoagro
        'ADAG3' => 'ADECOAGRO ON',

        // Klabin
        'KLBN3' => 'KLABIN ON',
        'KLBN4' => 'KLABIN PN',
        'KLBN11' => 'KLABIN (UNIT)',

        // Votorantim Cimentos
        'VOTU3' => 'VOTORANTIM CIMENTOS ON',
        'VOTO3' => 'VOTORANTIM CIMENTOS ON',

        // Randon
        'RODO3' => 'RANDON ON',
        'RODO4' => 'RANDON PN',

        // Terna
        'TRPN3' => 'TERNA ON',

        // Neoenergia
        'NEOE3' => 'NEOENERGIA ON',
        'NEOE4' => 'NEOENERGIA PN',

        // Energisa
        'ENGI3' => 'ENERGISA PN',
        'ENGI4' => 'ENERGISA ON',
        'ENGI11' => 'ENERGISA (UNIT)',
        'ENEV3' => 'ENERGISA MINAS GERAIS ON',
    ];

    /**
     * Normaliza o nome de uma empresa a partir do ticker e do nome bruto.
     * Retorna sempre em MAIÚSCULAS.
     *
     * @param  string      $ticker   Ticker do ativo (ex: ITSA4)
     * @param  string|null $rawName  Nome vindo da API
     * @return string|null Nome normalizado em MAIÚSCULAS
     */
    public static function normalize(string $ticker, ?string $rawName): ?string
    {
        $ticker = strtoupper(trim($ticker));

        // 1. Tenta mapeamento direto por ticker
        if (isset(self::TICKER_NAME_MAP[$ticker])) {
            return self::TICKER_NAME_MAP[$ticker];
        }

        // 2. Normaliza o nome vindo da API
        if ($rawName === null) {
            return null;
        }

        return self::cleanName($rawName);
    }

    /**
     * Normaliza strings de nome (espaços, formatos) e converte para MAIÚSCULAS.
     */
    private static function cleanName(string $name): string
    {
        // Remove espaços múltiplos
        $name = preg_replace('/\s+/', ' ', $name);

        // Trim
        $name = trim($name);

        // Normaliza formatos de "S.A." / "SA" / "S/A"
        $name = preg_replace('/\s*S\.?A\.?\s*$/i', ' SA', $name);
        $name = preg_replace('/\s*S\/A\s*$/i', ' SA', $name);

        // Normaliza "P.N" / "PN" / "ON"
        $name = preg_replace('/\s*P\.?N\.?\s*$/i', ' PN', $name);
        $name = preg_replace('/\s*O\.?N\.?\s*$/i', ' ON', $name);

        // Remove "(unit)" se existir
        $name = str_replace('(unit)', '', $name);
        $name = trim($name);

        // Converte para MAIÚSCULAS
        $name = mb_strtoupper($name, 'UTF-8');

        return $name;
    }
}
