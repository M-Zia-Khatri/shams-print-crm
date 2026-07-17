<?php

namespace App\Enums;

enum PaymentType: string
{
    case Salary = 'salary';
    case Advance = 'advance';
    case Bonus = 'bonus';
    case Deduction = 'deduction';

    public function label(): string
    {
        return match ($this) {
            self::Salary => 'Salary',
            self::Advance => 'Advance',
            self::Bonus => 'Bonus',
            self::Deduction => 'Deduction',
        };
    }
}
