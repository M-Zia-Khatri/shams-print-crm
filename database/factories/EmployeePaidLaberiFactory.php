<?php

namespace Database\Factories;

use App\Enums\PaymentType;
use App\Models\Employee;
use App\Models\EmployeePaidLaberi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmployeePaidLaberi>
 */
class EmployeePaidLaberiFactory extends Factory
{
    protected $model = EmployeePaidLaberi::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'amount' => fake()->randomFloat(2, 500, 5000),
            'paid_date' => fake()->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'payment_type' => fake()->randomElement(PaymentType::cases()),
            'reference_no' => fake()->optional()->bothify('REF-####'),
            'description' => fake()->optional()->sentence(),
        ];
    }
}
