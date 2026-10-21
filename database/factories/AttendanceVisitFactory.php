<?php

namespace Database\Factories;

use App\Models\AttendanceVisit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttendanceVisit>
 */
class AttendanceVisitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'checked_in_at' => now(),
            'checked_out_at' => null,
            'source' => 'manual',
            'device_identifier' => null,
            'idempotency_key' => fake()->uuid(),
            'session_consumed' => false,
            'override_reason' => null,
            'denial_reason' => null,
        ];
    }
}
