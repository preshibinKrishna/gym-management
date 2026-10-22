<?php

namespace Database\Factories;

use App\Models\GymEnquiry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GymEnquiry>
 */
class GymEnquiryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'gym_name' => fake()->unique()->company().' Fitness',
            'owner_name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('+91 9#########'),
            'city' => fake()->city(),
            'source' => fake()->randomElement(['Website', 'Referral', 'Instagram', 'Trade show', 'Partner']),
            'follow_up_on' => fake()->dateTimeBetween('-10 days', '+21 days')->format('Y-m-d'),
            'status' => fake()->randomElement(array_keys(GymEnquiry::STATUS_OPTIONS)),
            'notes' => fake()->sentence(),
        ];
    }
}
