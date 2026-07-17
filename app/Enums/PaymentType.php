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
            self::Bonus => 'Bonus (Award)',
            self::Deduction => 'Deduction',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Salary => 'Cash paid toward wages. Reduces remaining.',
            self::Advance => 'Prepayment given to the employee. Reduces remaining.',
            self::Bonus => 'Extra amount owed to the employee. Increases remaining. Record cash given for a bonus as Salary.',
            self::Deduction => 'Fine or clawback. Reduces remaining.',
        };
    }
}
