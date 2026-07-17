<?php

namespace App\Models;

use App\Enums\EmployeeRole;
use App\Enums\EmployeeStatus;
use Database\Factories\EmployeeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'name',
    'daily_laberi',
    'role',
    'phone_number',
    'status',
    'joining_date',
    'notes',
])]
class Employee extends Model
{
    /** @use HasFactory<EmployeeFactory> */
    use HasFactory, SoftDeletes;

    protected $attributes = [
        'status' => 'active',
    ];

    protected function casts(): array
    {
        return [
            'daily_laberi' => 'decimal:2',
            'role' => EmployeeRole::class,
            'status' => EmployeeStatus::class,
            'joining_date' => 'date',
        ];
    }

    public function dailyLaberiEntries(): HasMany
    {
        return $this->hasMany(EmployeeDailyLaberiEntry::class);
    }

    public function paidLaberi(): HasMany
    {
        return $this->hasMany(EmployeePaidLaberi::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', EmployeeStatus::Active);
    }
}
