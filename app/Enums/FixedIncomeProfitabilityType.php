<?php

namespace App\Enums;

enum FixedIncomeProfitabilityType: string
{
    case PREFIXED = 'prefixado';
    case POST_FIXED = 'pos_fixado';
    case HYBRID = 'hibrido';

    public function label(): string
    {
        return match ($this) {
            self::PREFIXED => 'Prefixado',
            self::POST_FIXED => 'Pós-fixado',
            self::HYBRID => 'Híbrido',
        };
    }

    /**
     * Opções prontas para uso nos formulários do Inertia.
     */
    public static function options(): array
    {
        return array_map(
            static fn (self $profitabilityType): array => [
                'value' => $profitabilityType->value,
                'label' => $profitabilityType->label(),
            ],
            self::cases(),
        );
    }
}
