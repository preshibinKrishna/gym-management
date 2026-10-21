<?php

namespace Database\Factories;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => fake()->numerify('9#########'),
            'email' => fake()->optional()->safeEmail(),
            'source' => fake()->randomElement(['walk-in', 'referral', 'instagram', 'website']),
            'follow_up_on' => now()->addDays(2)->toDateString(),
            'trial_starts_at' => now()->addDay()->setHour(8),
            'status' => 'not_contacted',
            'notes' => fake()->sentence(),
        ];
    }
}
