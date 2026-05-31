<?php

namespace App\Enums;

enum InvestmentAssetType: string
{
    case STOCK = 'acoes';
    case REAL_ESTATE_FUND = 'fiis';
    case ETF = 'etfs';
    case CRYPTOCURRENCY = 'criptomoedas';
    case TREASURY_BOND = 'tesouro_direto';
    case CDB = 'cdb';
    case LCI = 'lci';
    case LCA = 'lca';
    case LF = 'lf';
    case DEBENTURE = 'debentures';
    case INVESTMENT_FUND = 'fundos_investimento';

    public function label(): string
    {
        return match ($this) {
            self::STOCK => 'Ações',
            self::REAL_ESTATE_FUND => 'FIIs',
            self::ETF => 'ETFs',
            self::CRYPTOCURRENCY => 'Criptomoedas',
            self::TREASURY_BOND => 'Tesouro Direto',
            self::CDB => 'CDB',
            self::LCI => 'LCI',
            self::LCA => 'LCA',
            self::LF => 'LF',
            self::DEBENTURE => 'Debêntures',
            self::INVESTMENT_FUND => 'Fundos de Investimento',
        };
    }

    public function portfolioClass(): string
    {
        return match ($this) {
            self::STOCK => 'Ações',
            self::REAL_ESTATE_FUND => 'FIIs',
            self::ETF => 'ETFs',
            self::CRYPTOCURRENCY => 'Cripto',
            self::TREASURY_BOND,
            self::CDB,
            self::LCI,
            self::LCA,
            self::LF,
            self::DEBENTURE => 'Renda Fixa',
            self::INVESTMENT_FUND => 'Outros',
        };
    }

    public function isFixedIncome(): bool
    {
        return in_array($this, [
            self::TREASURY_BOND,
            self::CDB,
            self::LCI,
            self::LCA,
            self::LF,
            self::DEBENTURE,
        ], true);
    }

    /**
     * Opções prontas para uso nos formulários do Inertia.
     */
    public static function options(): array
    {
        return array_map(
            static fn (self $assetType): array => [
                'value' => $assetType->value,
                'label' => $assetType->label(),
                'portfolio_class' => $assetType->portfolioClass(),
                'is_fixed_income' => $assetType->isFixedIncome(),
            ],
            self::cases(),
        );
    }

    public static function fromLegacyCategoryName(?string $categoryName): self
    {
        $normalizedName = str($categoryName ?? '')
            ->ascii()
            ->lower()
            ->replace([' ', '-', '_'], '')
            ->toString();

        return match ($normalizedName) {
            'acoes', 'acao', 'stocks' => self::STOCK,
            'fii', 'fiis', 'fundosimobiliarios' => self::REAL_ESTATE_FUND,
            'etf', 'etfs' => self::ETF,
            'cripto', 'criptomoedas', 'crypto' => self::CRYPTOCURRENCY,
            'tesourodireto', 'tesouro' => self::TREASURY_BOND,
            'cdb' => self::CDB,
            'lci' => self::LCI,
            'lca' => self::LCA,
            'lf' => self::LF,
            'debenture', 'debentures' => self::DEBENTURE,
            default => self::INVESTMENT_FUND,
        };
    }
}
