<?php

namespace App\Enums;

enum ReleaseStatus: string
{
    case PENDING    = 'pending';
    case PAID       = 'paid';
    case CANCELED   = 'canceled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING    => 'Previsto',
            self::PAID       => 'Pago',
            self::CANCELED   => 'Cancelado',
        };
    }

    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case)
        {
            $options[$case->value] = $case->label();
        }
        return $options;
    }
}
