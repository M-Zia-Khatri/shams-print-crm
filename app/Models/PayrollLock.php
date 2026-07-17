<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'week_start_date',
    'week_end_date',
    'locked_by',
    'locked_at',
])]
class PayrollLock extends Model
{
    protected function casts(): array
    {
        return [
            'week_start_date' => 'date',
            'week_end_date' => 'date',
            'locked_at' => 'datetime',
        ];
    }

    public function lockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public static function isDateLocked(\DateTimeInterface|string $date): bool
    {
        return self::query()
            ->whereDate('week_start_date', '<=', $date)
            ->whereDate('week_end_date', '>=', $date)
            ->exists();
    }

    public static function findForWeek(\DateTimeInterface|string $weekStart, \DateTimeInterface|string $weekEnd): ?self
    {
        return self::query()
            ->whereDate('week_start_date', $weekStart)
            ->whereDate('week_end_date', $weekEnd)
            ->first();
    }

    public static function findOverlapping(\DateTimeInterface|string $start, \DateTimeInterface|string $end): ?self
    {
        return self::query()
            ->whereDate('week_start_date', '<=', $end)
            ->whereDate('week_end_date', '>=', $start)
            ->orderBy('week_start_date')
            ->first();
    }
}
