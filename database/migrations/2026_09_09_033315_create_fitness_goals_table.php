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
        Schema::create('fitness_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trainer_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('category');
            $table->string('title');
            $table->text('notes')->nullable();
            $table->date('starts_on');
            $table->date('target_on')->nullable();
            $table->date('review_on')->nullable();
            $table->string('status')->default('active');
            $table->json('milestones')->nullable();
            $table->index(['organization_id', 'member_id', 'status']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fitness_goals');
    }
};
