<?php

namespace Database\Factories;

use App\Models\BranchClosure;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BranchClosure>
 */
class BranchClosureFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'starts_on' => now()->addMonth()->toDateString(),
            'ends_on' => now()->addMonth()->toDateString(),
            'starts_at' => null,
            'ends_at' => null,
            'type' => 'closure',
            'title' => 'Development maintenance closure',
            'notes' => fake()->sentence(),
        ];
    }
}
