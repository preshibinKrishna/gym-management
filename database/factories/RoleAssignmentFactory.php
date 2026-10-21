<?php

namespace Database\Factories;

use App\Models\RoleAssignment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoleAssignment>
 */
class RoleAssignmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'starts_at' => now()->subDay(),
            'ends_at' => null,
        ];
    }
}
