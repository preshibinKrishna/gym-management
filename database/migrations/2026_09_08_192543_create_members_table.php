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
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('home_branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_trainer_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('member_code');
            $table->string('name');
            $table->string('avatar_path')->nullable();
            $table->date('date_of_birth');
            $table->string('gender')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->date('joined_on');
            $table->string('status')->default('active');
            $table->json('goals')->nullable();
            $table->json('consents')->nullable();
            $table->text('private_health_notes')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'member_code']);
            $table->index(['organization_id', 'home_branch_id', 'status']);
            $table->index(['organization_id', 'assigned_trainer_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
