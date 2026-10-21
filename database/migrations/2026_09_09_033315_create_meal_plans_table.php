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
        Schema::create('meal_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_user_id')->constrained('users')->restrictOnDelete();
            $table->string('name');
            $table->date('starts_on');
            $table->date('ends_on')->nullable();
            $table->json('meals');
            $table->json('preferences')->nullable();
            $table->string('status')->default('active');
            $table->unsignedInteger('version')->default(1);
            $table->index(['organization_id', 'member_id', 'status']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meal_plans');
    }
};
