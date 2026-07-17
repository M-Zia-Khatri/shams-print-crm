<?php

namespace Database\Factories;

use App\Enums\EmployeeRole;
use App\Enums\EmployeeStatus;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'daily_laberi' => fake()->randomFloat(2, 500, 2500),
            'role' => fake()->randomElement(EmployeeRole::cases()),
            'phone_number' => fake()->phoneNumber(),
            'status' => EmployeeStatus::Active,
            'joining_date' => fake()->dateTimeBetween('-2 years', 'now'),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EmployeeStatus::Inactive,
        ]);
    }
}
