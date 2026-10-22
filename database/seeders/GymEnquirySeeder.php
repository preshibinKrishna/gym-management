<?php

namespace Database\Seeders;

use App\Models\GymEnquiry;
use Illuminate\Database\Seeder;

class GymEnquirySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $enquiriesToCreate = max(0, 50 - GymEnquiry::query()->count());

        GymEnquiry::factory()->count($enquiriesToCreate)->create();
    }
}
