<?php

namespace Database\Factories;

use App\Models\GuardianRelationship;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GuardianRelationship>
 */
class GuardianRelationshipFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'relationship' => fake()->randomElement(['parent', 'guardian']),
            'can_view_profile' => true,
            'can_pay' => true,
            'revoked_at' => null,
            'review_required_at' => now()->addYears(6),
        ];
    }
}
