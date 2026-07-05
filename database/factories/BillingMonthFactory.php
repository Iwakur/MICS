<?php

namespace Database\Factories;

use App\Enums\BillingMonthStatus;
use App\Models\BillingMonth;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BillingMonth>
 */
class BillingMonthFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'month_date' => fake()->unique()->dateTimeBetween('-2 years', '+2 years')->format('Y-m-01'),
            'status' => BillingMonthStatus::Open,
            'closed_by_user_id' => null,
            'closed_at' => null,
        ];
    }
}
