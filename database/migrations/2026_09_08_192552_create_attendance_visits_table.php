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
        Schema::create('attendance_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('membership_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('operator_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('checked_in_at');
            $table->timestamp('checked_out_at')->nullable();
            $table->string('source')->default('manual');
            $table->string('device_identifier')->nullable();
            $table->string('idempotency_key');
            $table->boolean('session_consumed')->default(false);
            $table->string('override_reason')->nullable();
            $table->text('denial_reason')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'idempotency_key']);
            $table->index(['organization_id', 'branch_id', 'checked_in_at']);
            $table->index(['member_id', 'checked_out_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_visits');
    }
};
