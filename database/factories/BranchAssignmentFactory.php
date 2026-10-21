<?php

namespace Database\Factories;

use App\Models\BranchAssignment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BranchAssignment>
 */
class BranchAssignmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'status' => 'active',
            'starts_at' => now()->subDay(),
            'ends_at' => null,
        ];
    }
}
