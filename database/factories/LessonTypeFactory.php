<?php

namespace Database\Factories;

use App\Models\LessonType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LessonType>
 */
class LessonTypeFactory extends Factory
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
            'lesson_price' => fake()->randomFloat(2, 10, 100),
            'is_assignable' => true,
            'note' => fake()->optional()->sentence(),
        ];
    }
}
