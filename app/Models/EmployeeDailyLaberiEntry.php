<?php

namespace App\Models;

use App\Enums\DailyShift;
use Database\Factories\EmployeeDailyLaberiEntryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'employee_id',
    'laberi_date',
    'daily_shift',
])]
class EmployeeDailyLaberiEntry extends Model
{
    /** @use HasFactory<EmployeeDailyLaberiEntryFactory> */
    use HasFactory, SoftDeletes;

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
