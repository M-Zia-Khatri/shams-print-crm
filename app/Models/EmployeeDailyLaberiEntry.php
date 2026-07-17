<?php

namespace App\Models;

use App\Enums\DailyShift;
use Database\Factories\EmployeeDailyLaberiEntryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

#[Fillable([
    'employee_id',
    'laberi_date',
    'daily_shift',
])]
class EmployeeDailyLaberiEntry extends Model
{
    /** @use HasFactory<EmployeeDailyLaberiEntryFactory> */
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::saving(function (EmployeeDailyLaberiEntry $entry): void {
            if ($entry->deleted_at !== null) {
                $entry->active_laberi_key = null;

                return;
            }

            $date = $entry->laberi_date instanceof \DateTimeInterface
                ? $entry->laberi_date->format('Y-m-d')
                : (string) $entry->laberi_date;

            $entry->active_laberi_key = $entry->employee_id.'|'.$date;
        });

        static::deleting(function (EmployeeDailyLaberiEntry $entry): void {
            if ($entry->isForceDeleting()) {
                return;
            }

            DB::table($entry->getTable())
                ->where($entry->getKeyName(), $entry->getKey())
                ->update(['active_laberi_key' => null]);

            $entry->active_laberi_key = null;
        });
    }

    protected function casts(): array
    {
        return [
            'laberi_date' => 'date',
            'daily_shift' => DailyShift::class,
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function scopeBetweenDates($query, $start, $end)
    {
        return $query->whereBetween('laberi_date', [$start, $end]);
    }
}
