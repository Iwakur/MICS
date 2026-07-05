<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'duration_minutes' => fake()->randomElement([30, 45, 60, 90]),
            'lesson_count' => fake()->numberBetween(4, 20),
            'lesson_price' => fake()->randomFloat(2, 10, 100),
            'plan_price' => fake()->randomFloat(2, 100, 1000),
            'is_assignable' => true,
            'note' => fake()->optional()->sentence(),
        ];
    }
}
