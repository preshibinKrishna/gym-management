<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('einvoice_status')->default('not_requested')->after('tax_snapshot');
            $table->string('irn')->nullable()->unique();
            $table->json('einvoice_payload')->nullable();
            $table->json('einvoice_response')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropUnique(['irn']);
            $table->dropColumn(['einvoice_status', 'irn', 'einvoice_payload', 'einvoice_response']);
        });
    }
};
