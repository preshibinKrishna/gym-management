<?php

namespace Database\Factories;

use App\Models\BranchOperatingInterval;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BranchOperatingInterval>
 */
class BranchOperatingIntervalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'weekday' => fake()->numberBetween(1, 6),
            'opens_at' => '06:00:00',
            'closes_at' => '22:00:00',
            'closes_next_day' => false,
            'eligibility' => 'mixed',
            'effective_from' => now()->toDateString(),
            'effective_until' => null,
        ];
    }
}
