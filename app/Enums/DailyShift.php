<?php

namespace App\Enums;

enum DailyShift: string
{
    case Off = 'off';
    case Leave = 'leave';
    case Half = 'half';
    case Full = 'full';
    case Darid = 'darid';

    public function percentage(): float
    {
        return match ($this) {
            self::Off => 0.0,
            self::Leave => 0.0,
            self::Half => 0.5,
            self::Full => 1.0,
            self::Darid => 1.5,
        };
    }

    public function isWorkingDay(): bool
    {
        return ! in_array($this, [self::Off, self::Leave], true);
    }

    public function label(): string
    {
        return match ($this) {
            self::Off => 'Off',
            self::Leave => 'Leave',
            self::Half => 'Half Day',
            self::Full => 'Full Day',
            self::Darid => 'Darid',
        };
    }
}
