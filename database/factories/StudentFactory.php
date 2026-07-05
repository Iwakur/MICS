<?php

namespace Database\Factories;

use App\Enums\StudentBillingType;
use App\Enums\StudentStatus;
use App\Models\LessonType;
use App\Models\Plan;
use App\Models\Staff;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'staff_id' => Staff::factory(),
            'first_name' => fake()->firstName(),
            'family_name' => fake()->lastName(),
            'father_name' => fake()->optional()->firstNameMale(),
            'email' => fake()->optional()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'birthday' => fake()->optional()->dateTimeBetween('-25 years', '-5 years'),
            'city' => fake()->city(),
            'joined_at' => fake()->dateTimeBetween('-3 years'),
            'status' => StudentStatus::Active,
            'billing_type' => StudentBillingType::PerLesson,
            'lesson_type_id' => LessonType::factory(),
            'lesson_amount' => null,
            'plan_id' => null,
            'plan_start_at' => null,
            'discount_amount' => 0,
            'note' => fake()->optional()->sentence(),
        ];
    }

    public function planBased(): static
    {
        return $this->state(fn (array $attributes) => [
            'billing_type' => StudentBillingType::PlanBased,
            'lesson_type_id' => null,
            'lesson_amount' => null,
            'plan_id' => Plan::factory(),
            'plan_start_at' => now()->startOfMonth(),
        ]);
    }
}
