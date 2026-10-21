<?php

namespace Database\Factories;

use App\Models\MemberQrToken;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MemberQrToken>
 */
class MemberQrTokenFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'token_hash' => hash('sha256', fake()->uuid()),
            'expires_at' => now()->addMinutes(10),
            'revoked_at' => null,
            'used_at' => null,
        ];
    }
}
