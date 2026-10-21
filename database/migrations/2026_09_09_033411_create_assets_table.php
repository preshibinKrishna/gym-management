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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained()->nullOnDelete();
            $table->string('asset_tag');
            $table->string('name');
            $table->string('category');
            $table->json('muscle_groups')->nullable();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('zone')->nullable();
            $table->unsignedBigInteger('acquisition_cost_paise')->default(0);
            $table->date('purchased_on')->nullable();
            $table->date('warranty_until')->nullable();
            $table->string('condition')->default('working');
            $table->string('service_status')->default('current');
            $table->json('metadata')->nullable();
            $table->unique(['organization_id', 'asset_tag']);
            $table->index(['organization_id', 'branch_id', 'condition']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
