<?php

namespace Tests\Feature;

use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MySqlConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_mysql_supports_locked_invoice_reads(): void
    {
        if (config('database.default') !== 'mysql') {
            $this->markTestSkipped('Run this test with DB_CONNECTION=mysql against MySQL 8 for the database lock path.');
        }
        $organization = Organization::factory()->create();
        DB::transaction(function () use ($organization): void {
            $this->assertSame($organization->id, DB::table('organizations')->where('id', $organization->id)->lockForUpdate()->value('id'));
        });
    }
}
