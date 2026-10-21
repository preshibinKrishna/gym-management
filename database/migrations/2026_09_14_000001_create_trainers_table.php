<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trainers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('home_branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('specialization')->nullable();
            $table->date('joined_on');
            $table->string('employment_type')->default('salary');
            $table->unsignedBigInteger('salary_paise')->nullable();
            $table->unsignedBigInteger('daily_wage_paise')->nullable();
            $table->string('avatar_path')->nullable();
            $table->string('status')->default('active');
            $table->text('address')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'status']);
            $table->index(['organization_id', 'home_branch_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trainers');
    }
};
