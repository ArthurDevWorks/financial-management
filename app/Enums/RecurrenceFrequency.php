<?php

namespace App\Enums;

enum RecurrenceFrequency: string
{
    case WEEKLY         = 'weekly';
    case BIWEEKLY       = 'biweekly';
    case MONTHLY        = 'monthly';
    case QUARTERLY      = 'quarterly';
    case YEARLY         = 'yearly';

    public function label(): string
    {
        return match ($this) {
            self::WEEKLY         => 'Semanal',
            self::BIWEEKLY       => 'Quinzenal',
            self::MONTHLY        => 'Mensal',
            self::QUARTERLY      => 'Trimestral',
            self::YEARLY         => 'Anual',
        };
    }

    public function addToDate(\Carbon\Carbon $date): \Carbon\Carbon
    {
        return match ($this) {
            self::WEEKLY         => $date->addWeek(),
            self::BIWEEKLY       => $date->addWeeks(2),
            self::MONTHLY        => $date->addMonth(),
            self::QUARTERLY      => $date->addMonths(3),
            self::YEARLY         => $date->addYear(),
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
