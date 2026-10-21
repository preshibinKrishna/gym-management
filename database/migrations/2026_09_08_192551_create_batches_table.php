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
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trainer_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('code');
            $table->time('starts_at');
            $table->time('ends_at');
            $table->boolean('ends_next_day')->default(false);
            $table->unsignedInteger('capacity');
            $table->string('eligibility')->default('mixed');
            $table->json('weekdays')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->unique(['branch_id', 'code']);
            $table->index(['organization_id', 'branch_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};
