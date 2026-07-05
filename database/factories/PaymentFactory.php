<?php

namespace Database\Factories;

use App\Enums\ReviewStatus;
use App\Models\Payment;
use App\Models\StudentMonth;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_month_id' => StudentMonth::factory(),
            'paid_at' => fake()->dateTimeBetween('-2 years'),
            'amount' => fake()->randomFloat(2, 1, 500),
            'payment_method' => fake()->randomElement(['cash', 'bank_transfer', 'card']),
            'status' => ReviewStatus::Draft,
            'note' => fake()->optional()->sentence(),
        ];
    }

    public function validated(): static
    {
        return $this->state(fn (array $attributes) => ['status' => ReviewStatus::Validated]);
    }
}
