<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CASH           = 'cash';
    case CREDIT_CARD    = 'credit_card';
    case DEBIT_CARD     = 'debit_card';
    case PIX            = 'pix';

    public function label(): string
    {
        return match ($this) {
            self::CASH           => 'Dinheiro',
            self::CREDIT_CARD    => 'Cartão de Crédito',
            self::DEBIT_CARD     => 'Cartão de Débito',
            self::PIX            => 'Pix',
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
