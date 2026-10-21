<?php

namespace Database\Factories;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Organization>
 */
class OrganizationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'code' => fake()->unique()->bothify('GYM-###'),
            'legal_name' => fake()->company().' Private Limited',
            'status' => 'active',
            'currency' => 'INR',
            'default_timezone' => 'Asia/Kolkata',
            'settings' => [
                'collection_policy' => 'manual_collections_enabled',
                'access_policy' => 'allow_until_due_date',
            ],
        ];
    }
}
