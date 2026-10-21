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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->nullable()->constrained()->nullOnDelete();
            $table->string('number')->nullable();
            $table->string('status')->default('draft');
            $table->date('issued_on')->nullable();
            $table->date('due_on')->nullable();
            $table->unsignedBigInteger('subtotal_paise')->default(0);
            $table->unsignedBigInteger('discount_paise')->default(0);
            $table->unsignedBigInteger('tax_paise')->default(0);
            $table->unsignedBigInteger('total_paise')->default(0);
            $table->unsignedBigInteger('paid_paise')->default(0);
            $table->json('tax_snapshot')->nullable();
            $table->unique(['organization_id', 'number']);
            $table->index(['organization_id', 'status', 'due_on']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
