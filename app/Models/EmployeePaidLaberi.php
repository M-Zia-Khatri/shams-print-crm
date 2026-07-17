<?php

namespace App\Models;

use App\Enums\PaymentType;
use Database\Factories\EmployeePaidLaberiFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'employee_id',
    'amount',
    'paid_date',
    'payment_type',
    'reference_no',
    'description',
])]
class EmployeePaidLaberi extends Model
{
    /** @use HasFactory<EmployeePaidLaberiFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_date' => 'date',
            'payment_type' => PaymentType::class,
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function scopeBetweenDates($query, $start, $end)
    {
        return $query->whereBetween('paid_date', [$start, $end]);
    }
}
