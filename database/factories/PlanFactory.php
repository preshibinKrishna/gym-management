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
            'name' => fake()->randomElement(['Monthly Gym Access', 'Quarterly Strength', 'Ten Session PT Pack']),
            'code' => fake()->unique()->bothify('PLAN-###'),
            'type' => 'membership',
            'duration_days' => null,
            'duration_months' => 1,
            'session_limit' => null,
            'freeze_days_allowed' => 7,
            'price_paise' => 250000,
            'joining_fee_paise' => 0,
            'age_group' => 'adult',
            'eligible_branch_ids' => null,
            'features' => ['gym floor access'],
            'is_active' => true,
        ];
    }

    public function sessionPack(int $sessions = 10): static
    {
        return $this->state(fn (array $attributes): array => [
            'name' => $sessions.' Session Pack',
            'type' => 'session_pack',
            'duration_months' => null,
            'duration_days' => 90,
            'session_limit' => $sessions,
            'freeze_days_allowed' => 0,
        ]);
    }
}
