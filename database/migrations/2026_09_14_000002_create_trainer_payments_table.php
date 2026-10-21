<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trainer_payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trainer_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('amount_paise');
            $table->string('payment_type')->default('salary');
            $table->string('method')->default('bank_transfer');
            $table->date('paid_on');
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'trainer_id', 'paid_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trainer_payments');
    }
};
