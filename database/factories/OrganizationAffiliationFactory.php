<?php

namespace Database\Factories;

use App\Models\OrganizationAffiliation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrganizationAffiliation>
 */
class OrganizationAffiliationFactory extends Factory
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
            'verified_at' => now(),
        ];
    }
}
