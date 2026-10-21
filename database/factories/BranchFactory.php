<?php

namespace Database\Factories;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Branch>
 */
class BranchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->city().' Fitness',
            'code' => fake()->unique()->lexify('???'),
            'status' => 'active',
            'timezone' => 'Asia/Kolkata',
            'phone' => fake()->numerify('9#########'),
            'email' => fake()->companyEmail(),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'state' => fake()->state(),
            'postal_code' => fake()->postcode(),
            'capacity' => fake()->numberBetween(75, 350),
            'facilities' => ['strength', 'cardio', 'functional'],
            'terms_summary' => 'Development branch terms and waiver summary.',
        ];
    }
}
