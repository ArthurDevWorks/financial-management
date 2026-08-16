<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Scraping do Fundamentus (fundamentus.com.br) como fonte complementar
 * de indicadores fundamentalistas para ações brasileiras.
 */
class FundamentusScraperService
{
    private const BASE_URL = 'https://www.fundamentus.com.br/detalhes.php';

    private const HEADERS = [
        'User-Agent'      => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36',
        'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language' => 'pt-BR,pt;q=0.9,en-US;q=0.8',
        'Referer'         => 'https://www.fundamentus.com.br/',
    ];

    public function fetchStockIndicators(string $ticker): ?array
    {
        $ticker = strtoupper(trim($ticker));

        try {
            $response = Http::withHeaders(self::HEADERS)
                ->timeout(20)
                ->get(self::BASE_URL, ['papel' => $ticker]);

            if ($response->failed()) {
                Log::warning("Fundamentus fetch failed {$ticker}: HTTP {$response->status()}");

                return null;
            }

            $html = mb_convert_encoding($response->body(), 'UTF-8', 'ISO-8859-1');

            return $this->parseHtml($html, $ticker);
        } catch (\Throwable $e) {
            Log::warning("Fundamentus error {$ticker}: {$e->getMessage()}");

            return null;
        }
    }

    private function parseHtml(string $html, string $ticker): ?array
    {
        preg_match_all(
            '/<td[^>]+class="label"[^>]*>(.*?)<\/td>\s*<td[^>]+class="data[^"]*"[^>]*>(?:<[^>]+>)?(.*?)(?:<\/[^>]+>)?<\/td>/si',
            $html,
            $matches
        );

        if (empty($matches[1])) {
            return null;
        }

        $raw = [];
        for ($i = 0; $i < count($matches[1]); $i++) {
            $label = $this->normalizeLabel(strip_tags($matches[1][$i]));
            $value = $this->cleanValue(strip_tags($matches[2][$i]));
            if ($label && $value !== null) {
                $raw[$label] = $value;
            }
        }

        if (empty($raw)) {
            return null;
        }

        $g = fn (string $key) => $raw[$key] ?? null;

        return array_filter([
            'dividend_yield'     => $g('div_yield'),
            'price_to_earnings'  => $g('p_l'),
            'price_to_book'      => $g('p_vp'),
            'price_to_sales'     => $g('psr'),
            'ev_to_ebitda'       => $g('ev_ebitda'),
            'roe'                => $g('roe'),
            'roic'               => $g('roic'),
            'profit_margin'      => $g('marg_liquida'),
            'ebitda_margin'      => $g('marg_ebit'),
            'gross_margin'       => $g('marg_bruta'),
            'current_liquidity'  => $g('liquidez_corr'),
            'total_shares'       => $g('nro_acoes'),
            'net_income'         => $g('lucro_liquido'),
            'net_debt'           => $g('div_liquida'),
            'net_worth'          => $g('patrim_liq'),
        ], fn ($v) => $v !== null);
    }

    private function normalizeLabel(string $label): string
    {
        $label = preg_replace('/^\?+/', '', trim($label));
        $label = strtolower($label);

        // Subtstitui caracteres acentuados comuns
        $label = str_replace(
            ['á', 'à', 'ã', 'â', 'é', 'ê', 'í', 'ó', 'ô', 'õ', 'ú', 'ü', 'ç'],
            ['a', 'a', 'a', 'a', 'e', 'e', 'i', 'o', 'o', 'o', 'u', 'u', 'c'],
            $label
        );

        $label = preg_replace('/[^a-z0-9]+/', '_', $label);
        $label = trim($label, '_');
        $label = preg_replace('/_+/', '_', $label);

        $map = [
            'div_yield'      => 'div_yield',
            'p_l'            => 'p_l',
            'p_vp'           => 'p_vp',
            'psr'            => 'psr',
            'ev_ebitda'      => 'ev_ebitda',
            'ev_ebit'        => 'ev_ebitda',
            'roe'            => 'roe',
            'roic'           => 'roic',
            'marg_liquida'   => 'marg_liquida',
            'marg_liq'       => 'marg_liquida',
            'marg_ebit'      => 'marg_ebit',
            'marg_bruta'     => 'marg_bruta',
            'liquidez_corr'  => 'liquidez_corr',
            'nro_acoes'      => 'nro_acoes',
            'lucro_liquido'  => 'lucro_liquido',
            'div_liquida'    => 'div_liquida',
            'patrim_liq'     => 'patrim_liq',
        ];

        return $map[$label] ?? $label;
    }

    private function cleanValue(string $value): mixed
    {
        $value = trim($value);

        if ($value === '' || $value === '-' || $value === '--' || $value === 'N/A') {
            return null;
        }

        $isPercent = str_ends_with($value, '%');

        $clean = preg_replace('/[\s\xA0\x{00A0}]+/u', '', $value);
        if ($clean === null) {
            return null;
        }

        $clean = str_replace(['%', 'R$', '$'], '', $clean);

        if (str_contains($clean, ',')) {
            $clean = str_replace('.', '', $clean);
            $clean = str_replace(',', '.', $clean);
        } else {
            $withoutDots = str_replace('.', '', $clean);
            if (is_numeric($withoutDots)) {
                $clean = $withoutDots;
            }
        }

        if (is_numeric($clean)) {
            $num = (float) $clean;

            return $isPercent ? round($num, 4) : $num;
        }

        return null;
    }
}
