<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\StudentMonth;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentMonth>
 */
class StudentMonthFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'month_date' => fake()->unique()->dateTimeBetween('-2 years', '+2 years')->format('Y-m-01'),
            'lesson_count' => fake()->numberBetween(0, 20),
            'opening_balance' => fake()->randomFloat(2, 0, 500),
            'charge_amount' => fake()->randomFloat(2, 0, 500),
            'manual_adjustment' => 0,
            'note' => fake()->optional()->sentence(),
        ];
    }
}
