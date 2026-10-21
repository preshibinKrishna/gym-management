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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code');
            $table->string('type')->default('membership');
            $table->unsignedInteger('duration_days')->nullable();
            $table->unsignedInteger('duration_months')->nullable();
            $table->unsignedInteger('session_limit')->nullable();
            $table->unsignedInteger('freeze_days_allowed')->default(0);
            $table->unsignedBigInteger('price_paise');
            $table->unsignedBigInteger('joining_fee_paise')->default(0);
            $table->string('age_group')->default('adult');
            $table->json('eligible_branch_ids')->nullable();
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['organization_id', 'code']);
            $table->index(['organization_id', 'is_active', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
