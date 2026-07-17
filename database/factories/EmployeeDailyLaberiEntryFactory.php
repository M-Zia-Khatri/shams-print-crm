<?php

namespace Database\Factories;

use App\Enums\DailyShift;
use App\Models\Employee;
use App\Models\EmployeeDailyLaberiEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmployeeDailyLaberiEntry>
 */
class EmployeeDailyLaberiEntryFactory extends Factory
{
    protected $model = EmployeeDailyLaberiEntry::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'laberi_date' => fake()->unique()->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'daily_shift' => fake()->randomElement(DailyShift::cases()),
        ];
    }
}
