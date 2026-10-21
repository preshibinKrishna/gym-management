<?php

namespace Database\Factories;

use App\Models\Batch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Batch>
 */
class BatchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Morning Strength', 'Evening Conditioning']),
            'code' => fake()->unique()->bothify('B##'),
            'starts_at' => '06:00:00',
            'ends_at' => '07:00:00',
            'ends_next_day' => false,
            'capacity' => 20,
            'eligibility' => 'mixed',
            'weekdays' => [1, 2, 3, 4, 5, 6],
            'status' => 'active',
        ];
    }
}
