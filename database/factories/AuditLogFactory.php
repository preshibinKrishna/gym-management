<?php

namespace Database\Factories;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event' => 'development.seeded',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Seeder',
            'reason' => 'Development data',
            'metadata' => ['environment' => 'local'],
        ];
    }
}
