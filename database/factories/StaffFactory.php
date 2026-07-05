<?php

namespace Database\Factories;

use App\Enums\StaffCompensationMode;
use App\Models\Staff;
use App\Models\StaffRole;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Staff>
 */
class StaffFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'staff_role_id' => StaffRole::factory(),
            'first_name' => fake()->firstName(),
            'family_name' => fake()->lastName(),
            'father_name' => fake()->optional()->firstNameMale(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'birthday' => fake()->optional()->dateTimeBetween('-65 years', '-20 years'),
            'city' => fake()->city(),
            'payout_card_number' => fake()->optional()->numerify('################'),
            'compensation_mode' => StaffCompensationMode::Fixed,
            'salary_amount' => fake()->randomFloat(2, 500, 5000),
            'is_active' => true,
            'note' => fake()->optional()->sentence(),
        ];
    }
}
