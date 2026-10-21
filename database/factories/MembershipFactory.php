<?php

namespace Database\Factories;

use App\Models\Membership;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Membership>
 */
class MembershipFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'starts_on' => now()->toDateString(),
            'ends_on' => now()->addMonthNoOverflow()->subDay()->toDateString(),
            'status' => 'active',
            'sessions_total' => null,
            'sessions_used' => 0,
            'freeze_days_used' => 0,
            'price_paise' => 250000,
            'discount_paise' => 0,
            'tax_paise' => 0,
            'total_paise' => 250000,
            'plan_snapshot' => ['name' => 'Monthly Gym Access', 'duration_months' => 1],
            'allowed_branch_ids' => [],
            'notes' => null,
        ];
    }
}
