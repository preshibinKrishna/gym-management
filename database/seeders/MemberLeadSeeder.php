<?php

namespace Database\Seeders;

use App\Models\Lead;
use App\Models\Organization;
use Illuminate\Database\Seeder;

class MemberLeadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Organization::query()
            ->with('branches')
            ->orderBy('code')
            ->get()
            ->each(function (Organization $organization): void {
                $leadsToCreate = max(0, 50 - Lead::query()->whereBelongsTo($organization)->count());

                Lead::factory()->count($leadsToCreate)->create([
                    'organization_id' => $organization->id,
                    'branch_id' => $organization->branches->first()?->id,
                ]);
            });
    }
}
