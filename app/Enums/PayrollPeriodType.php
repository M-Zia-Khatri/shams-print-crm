<?php

namespace App\Enums;

enum PayrollPeriodType: string
{
    case Weekly = 'weekly';
    case Monthly = 'monthly';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::Weekly => 'Weekly',
            self::Monthly => 'Monthly',
            self::Custom => 'Custom',
        };
    }
}
