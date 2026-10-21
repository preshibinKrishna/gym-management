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
        Schema::create('gst_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('legal_name');
            $table->string('gstin')->nullable();
            $table->string('state_code', 2);
            $table->boolean('enabled')->default(false);
            $table->date('effective_from');
            $table->date('effective_until')->nullable();
            $table->json('settings')->nullable();
            $table->index(['organization_id', 'effective_from']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gst_settings');
    }
};
