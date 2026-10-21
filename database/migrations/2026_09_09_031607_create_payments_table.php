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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('amount_paise');
            $table->string('method');
            $table->string('provider_reference')->nullable();
            $table->string('idempotency_key');
            $table->date('received_on');
            $table->string('status')->default('received');
            $table->text('notes')->nullable();
            $table->unique(['organization_id', 'idempotency_key']);
            $table->index(['organization_id', 'received_on']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
