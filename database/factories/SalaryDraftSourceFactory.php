<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\SalaryDraftSource;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SalaryDraftSource>
 */
class SalaryDraftSourceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'expense_id' => Expense::factory(),
            'student_id' => Student::factory(),
            'source_type' => 'per_lesson',
            'description' => fake()->sentence(3),
            'units' => 4,
            'rate' => 20,
            'amount' => 80,
        ];
    }
}
