<?php

namespace Database\Factories;

use App\Models\BankMonth;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BankMonth>
 */
class BankMonthFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'month_date' => fake()->unique()->dateTimeBetween('-5 years', '+2 years')->format('Y-m-01'),
            'opening_balance' => fake()->randomFloat(2, 0, 10000),
            'closing_balance' => fake()->randomFloat(2, 0, 10000),
            'expected_closing_balance' => 0,
            'status' => 'draft',
            'note' => fake()->optional()->sentence(),
        ];
    }
}
