<?php

namespace Database\Factories;

use App\Models\BatchEnrollment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BatchEnrollment>
 */
class BatchEnrollmentFactory extends Factory
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
            'starts_on' => now()->toDateString(),
            'ends_on' => null,
        ];
    }
}
