<?php

namespace Database\Factories;

use App\Models\BillingMonth;
use App\Models\BillingMonthEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BillingMonthEvent>
 */
class BillingMonthEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'billing_month_id' => BillingMonth::factory(),
            'user_id' => User::factory()->admin(),
            'action' => 'closed',
            'reason' => null,
            'occurred_at' => now(),
        ];
    }
}
