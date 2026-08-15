<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Investidor10ScraperService
{
    private const HEADERS = [
        'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language' => 'pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7',
        'Referer'         => 'https://investidor10.com.br/',
        'User-Agent'      => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36',
    ];

    public function fetchStockIndicators(string $ticker): ?array
    {
        $ticker = strtoupper(trim($ticker));
        $html   = $this->fetchPage("https://investidor10.com.br/acoes/{$ticker}/");

        if ($html === null) {
            return null;
        }

        return $this->parseFromEmbeddedJson($html);
    }

    public function fetchFiiIndicators(string $ticker): ?array
    {
        $ticker = strtoupper(trim($ticker));
        $html   = $this->fetchPage("https://investidor10.com.br/fiis/{$ticker}/");

        if ($html === null) {
            return null;
        }

        return $this->parseFiiFromEmbeddedJson($html);
    }

    private function fetchPage(string $url): ?string
    {
        try {
            $response = Http::withHeaders(self::HEADERS)
                ->timeout(15)
                ->get($url);

            if ($response->failed()) {
                return null;
            }

            return $response->body();
        } catch (\Throwable $e) {
            Log::warning("Investidor10 fetch error: {$e->getMessage()}");

            return null;
        }
    }

    /**
     * Extrai indicadores fundamentalistas do JSON embutido na página.
     * O Investidor10 embute os dados como pares "chave":valor no HTML.
     */
    private function parseFromEmbeddedJson(string $html): array
    {
        $n = fn (string $key) => $this->extractFloat($key, $html);

        $data = array_filter([
            // Preço e cotação
            'current_price'        => $n('price'),

            // Valuation
            'price_to_earnings'    => $n('p_l'),
            'price_to_book'        => $n('p_vp'),
            'price_to_sales'       => $n('psr'),
            'price_to_assets'      => $n('p_assets'),
            'price_to_cash_flow'   => $n('p_working_capital'),
            'ev_to_ebitda'         => $n('ev_ebitda'),

            // Rentabilidade
            'roe'                  => $n('roe'),
            'roa'                  => $n('roa'),
            'roic'                 => $n('roic'),
            'profit_margin'        => $n('net_margin'),
            'ebitda_margin'        => $n('ebitda_margin'),
            'gross_margin'         => $n('gross_margin'),
            'ebitda_margin_alt'    => $n('ebit_margin'),     // fallback

            // Dívida e endividamento
            'net_debt_to_ebitda'   => $n('net_debt_ebitda'),
            'current_liquidity'    => $n('current_liquidity'),

            // Dados do balanço
            'ebitda'               => $n('balance_ebitda'),
            'net_debt'             => $n('balance_net_debt'),
            'gross_debt'           => $n('balance_gross_debt'),
            'net_income'           => $n('balance_net_profit'),
            'revenue'              => $n('net_revenue'),
            'net_worth'            => $n('balance_net_worth'),

            // Dividendos
            'payout'               => $n('payout'),
            'dividend_yield'       => $n('dividend_yield_last_12_months'),

            // Mercado
            'market_cap'           => $n('market_value'),
            'enterprise_value'     => $n('enterprise_value'),

            // Por ação
            'earnings_per_share'   => $n('lpa'),
            'book_value_per_share' => $n('vpa'),
            'total_shares'         => $this->extractInt('total_tickers', $html),
        ], fn ($v) => $v !== null);

        // Remove campo auxiliar gerado internamente
        unset($data['ebitda_margin_alt']);

        return $data;
    }

    private function parseFiiFromEmbeddedJson(string $html): array
    {
        $n = fn (string $key) => $this->extractFloat($key, $html);

        return array_filter([
            'current_price'        => $n('price'),
            'dividend_yield'       => $n('dividend_yield_last_12_months'),
            'price_to_book'        => $n('p_vp'),
            'p_vp'                 => $n('p_vp'),
            'net_worth'            => $n('balance_net_worth'),
            'book_value_per_share' => $n('vpa'),
            'market_cap'           => $n('market_value'),
            'payout'               => $n('payout'),
            'vacancy_rate'         => $n('vacancy_rate'),
            'vacancy_financial'    => $n('vacancy_financial'),
            'cap_rate'             => $n('cap_rate'),
            'number_of_properties' => $this->extractInt('number_of_properties', $html),
            'total_shares'         => $this->extractInt('total_tickers', $html),
        ], fn ($v) => $v !== null);
    }

    /**
     * Extrai um valor float de um JSON embutido no HTML pelo nome da chave.
     */
    private function extractFloat(string $key, string $html): ?float
    {
        $pattern = '/"' . preg_quote($key, '/') . '"\s*:\s*([\-]?[\d]+(?:\.[\d]+)?)/';

        if (preg_match($pattern, $html, $m)) {
            return (float) $m[1];
        }

        return null;
    }

    private function extractInt(string $key, string $html): ?int
    {
        $v = $this->extractFloat($key, $html);

        return $v !== null ? (int) round($v) : null;
    }
}
