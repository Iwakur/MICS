<?php

namespace Database\Factories;

use App\Enums\ReviewStatus;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Expense>
 */
class ExpenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'staff_id' => null,
            'expense_category_id' => ExpenseCategory::factory(),
            'month_date' => fake()->dateTimeBetween('-2 years')->format('Y-m-01'),
            'amount' => fake()->randomFloat(2, 1, 1000),
            'status' => ReviewStatus::Draft,
            'is_auto_generated' => false,
            'note' => fake()->optional()->sentence(),
        ];
    }

    public function validated(): static
    {
        return $this->state(fn (array $attributes) => ['status' => ReviewStatus::Validated]);
    }
}
