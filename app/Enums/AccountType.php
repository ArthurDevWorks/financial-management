<?php

namespace App\Enums;

enum AccountType: string
{
    case CHECKING = 'corrente';
    case SAVINGS = 'poupanca';
    case DIGITAL = 'digital';
    case INVESTMENT = 'investimento';
    case SALARY = 'salario';

    public function label(): string
    {
        return match($this) {
            self::CHECKING => 'Conta Corrente',
            self::SAVINGS => 'Poupança',
            self::DIGITAL => 'Conta Digital',
            self::INVESTMENT => 'Conta de Investimento',
            self::SALARY => 'Conta Salário',
        };
    }

    public static function options(): array
    {
        return [
            self::CHECKING->value => self::CHECKING->label(),
            self::SAVINGS->value => self::SAVINGS->label(),
            self::DIGITAL->value => self::DIGITAL->label(),
            self::INVESTMENT->value => self::INVESTMENT->label(),
            self::SALARY->value => self::SALARY->label(),
        ];
    }
}
