<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('members', 'avatar_path')) {
            Schema::table('members', function (Blueprint $table): void {
                $table->string('avatar_path')->nullable()->after('name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('members', 'avatar_path')) {
            Schema::table('members', function (Blueprint $table): void {
                $table->dropColumn('avatar_path');
            });
        }
    }
};
