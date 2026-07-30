<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Investidor10ScraperService
{
    public function fetchStockIndicators(string $ticker): ?array
    {
        $ticker = strtoupper(trim($ticker));
        $html = $this->fetchPage("https://investidor10.com.br/acoes/{$ticker}/");

        if ($html === null) {
            return null;
        }

        return $this->parseIndicators($html);
    }

    public function fetchFiiIndicators(string $ticker): ?array
    {
        $ticker = strtoupper(trim($ticker));
        $html = $this->fetchPage("https://investidor10.com.br/fiis/{$ticker}/");

        if ($html === null) {
            return null;
        }

        return $this->parseIndicators($html);
    }

    private function fetchPage(string $url): ?string
    {
        try {
            $response = Http::withHeaders([
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7',
                'Referer' => 'https://investidor10.com.br/',
                'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36',
            ])
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

    private function parseIndicators(string $html): array
    {
        $doc = new \DOMDocument();

        @$doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));

        $xpath = new \DOMXPath($doc);

        $indicators = [];
        $nodes = $xpath->query("//div[contains(@class, '_card-body_') or contains(@class, 'indicator') or contains(@class, 'value')]");

        foreach ($nodes as $node) {
            $text = trim($node->textContent);
            $text = preg_replace('/\s+/', ' ', $text);

            $parent = $node->parentNode;
            if ($parent) {
                $labelNode = $xpath->query(".//*[contains(@class, 'label') or contains(@class, 'title')]", $parent);
                $label = $labelNode && $labelNode->length > 0
                    ? trim($labelNode->item(0)->textContent)
                    : '';
                $label = preg_replace('/\s+/', ' ', $label);

                $value = $text;
                if (!empty($label)) {
                    $indicators[$this->normalizeLabel($label)] = $this->cleanValue($value);
                }
            }
        }

        $scriptBased = $this->extractFromScripts($html);

        return array_merge($indicators, $scriptBased);
    }

    private function extractFromScripts(string $html): array
    {
        $data = [];

        if (preg_match('/"currentPrice"\s*:\s*([\d.]+)/', $html, $m)) {
            $data['current_price'] = (float) $m[1];
        }

        if (preg_match('/"dividendYield"\s*:\s*([\d.]+)/', $html, $m)) {
            $data['dividend_yield'] = (float) $m[1];
        }

        if (preg_match('/"priceToEarnings"\s*:\s*([\d.]+)/', $html, $m)) {
            $data['price_to_earnings'] = (float) $m[1];
        }

        if (preg_match('/"priceToBook"\s*:\s*([\d.]+)/', $html, $m)) {
            $data['price_to_book'] = (float) $m[1];
        }

        if (preg_match('/"returnOnEquity"\s*:\s*([\d.]+)/', $html, $m)) {
            $data['roe'] = (float) $m[1];
        }

        if (preg_match('/"marketCap"\s*:\s*([\d.]+)/', $html, $m)) {
            $data['market_cap'] = (float) $m[1];
        }

        if (preg_match('/"totalShares"\s*:\s*(\d+)/', $html, $m)) {
            $data['total_shares'] = (int) $m[1];
        }

        if (preg_match('/"payout"\s*:\s*([\d.]+)/', $html, $m)) {
            $data['payout'] = (float) $m[1];
        }

        return $data;
    }

    private function normalizeLabel(string $label): string
    {
        $label = strtolower(trim($label));
        $label = preg_replace('/[^a-z0-9\x{00E0}-\x{00FC}]/u', '_', $label);
        $label = preg_replace('/_+/', '_', $label);
        $label = trim($label, '_');

        $map = [
            'cotacao' => 'current_price',
            'dividend_yield' => 'dividend_yield',
            'p_l' => 'price_to_earnings',
            'p_vp' => 'price_to_book',
            'roe' => 'roe',
            'roa' => 'roa',
            'margem_liquida' => 'profit_margin',
            'margem_ebit' => 'ebitda_margin',
            'margem_bruta' => 'gross_margin',
            'payout' => 'payout',
            'valor_de_mercado' => 'market_cap',
            'liquidez_media' => 'volume_avg_30d',
            'vagas_fisicas' => 'vacancy_rate',
            'vagas_financeiras' => 'vacancy_financial',
            'cap_rate' => 'cap_rate',
            'numero_de_imoveis' => 'number_of_properties',
            'patrimonio_liquido' => 'net_worth',
            'p_ativo' => 'price_to_assets',
            'p_cap_giro' => 'price_to_cash_flow',
            'ev_ebit' => 'ev_to_ebitda',
            'p_sr' => 'price_to_sales',
            'divida_liquida_ebitda' => 'net_debt_to_ebitda',
            'liquidez_corrente' => 'current_liquidity',
            'dy' => 'dividend_yield',
        ];

        return $map[$label] ?? $label;
    }

    private function cleanValue(string $value): mixed
    {
        $value = trim($value);

        if ($value === '' || $value === '-' || $value === '--') {
            return null;
        }

        $isPercent = str_ends_with($value, '%');
        $clean = str_replace(['%', 'R$', ' ', '.'], ['', '', '', ''], $value);
        $clean = str_replace(',', '.', $clean);

        if (is_numeric($clean)) {
            $num = (float) $clean;
            if ($isPercent) {
                return round($num, 4);
            }
            return $num;
        }

        return $value;
    }
}
