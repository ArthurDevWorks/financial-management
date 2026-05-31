<?php

namespace App\Enums;

enum FixedIncomeIndexer: string
{
    case CDI = 'cdi';
    case IPCA = 'ipca';
    case SELIC = 'selic';
    case IGP_M = 'igp_m';
    case PREFIXED = 'pre_fixado';

    public function label(): string
    {
        return match ($this) {
            self::CDI => 'CDI',
            self::IPCA => 'IPCA',
            self::SELIC => 'SELIC',
            self::IGP_M => 'IGP-M',
            self::PREFIXED => 'Pré-fixado',
        };
    }

    /**
     * Opções prontas para uso nos formulários do Inertia.
     */
    public static function options(): array
    {
        return array_map(
            static fn (self $indexer): array => [
                'value' => $indexer->value,
                'label' => $indexer->label(),
            ],
            self::cases(),
        );
    }
}
