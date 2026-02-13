<?php

namespace App\Enums;

enum CategoryType: string
{
    case REVENUE = 'receita';
    case EXPENSE = 'despesa';
    case INVESTMENT = 'investimento';

    public function label(): string
    {
        return match($this) {
            self::REVENUE => 'Receita',
            self::EXPENSE => 'Despesa',
            self::INVESTMENT => 'Investimento',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::REVENUE => 'bg-green-500/20 text-green-400 border border-green-500/50',
            self::EXPENSE => 'bg-red-500/20 text-red-400 border border-red-500/50',
            self::INVESTMENT => 'bg-purple-500/20 text-purple-400 border border-purple-500/50',
        };
    }

    public static function all(): array
    {
        return [
            self::REVENUE->value => self::REVENUE->label(),
            self::EXPENSE->value => self::EXPENSE->label(),
            self::INVESTMENT->value => self::INVESTMENT->label(),
        ];
    }
}
