<?php

namespace Database\Factories;

use App\Models\MembershipFreeze;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MembershipFreeze>
 */
class MembershipFreezeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'starts_on' => now()->addWeek()->toDateString(),
            'ends_on' => now()->addWeek()->addDays(2)->toDateString(),
            'status' => 'approved',
            'reason' => 'Travel',
        ];
    }
}
