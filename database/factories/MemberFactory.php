<?php

namespace Database\Factories;

use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Member>
 */
class MemberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'member_code' => fake()->unique()->bothify('M-####'),
            'name' => fake()->name(),
            'date_of_birth' => fake()->dateTimeBetween('-45 years', '-18 years')->format('Y-m-d'),
            'gender' => fake()->randomElement(['female', 'male', null]),
            'phone' => fake()->numerify('9#########'),
            'email' => fake()->optional()->safeEmail(),
            'address' => fake()->address(),
            'emergency_contact_name' => fake()->name(),
            'emergency_contact_phone' => fake()->numerify('9#########'),
            'joined_on' => now()->subMonth()->toDateString(),
            'status' => 'active',
            'goals' => ['general fitness'],
            'consents' => ['waiver_v1' => now()->toDateString()],
            'private_health_notes' => null,
        ];
    }

    public function child(): static
    {
        return $this->state(fn (array $attributes): array => [
            'date_of_birth' => now()->subYears(12)->toDateString(),
        ]);
    }
}
