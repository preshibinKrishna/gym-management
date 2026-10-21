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
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('amount_paise');
            $table->string('method')->default('original');
            $table->string('status')->default('requested');
            $table->string('idempotency_key');
            $table->text('reason');
            $table->timestamp('refunded_at')->nullable();
            $table->unique(['organization_id', 'idempotency_key']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
