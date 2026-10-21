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
        Schema::create('memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payer_member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->date('starts_on');
            $table->date('ends_on');
            $table->string('status')->default('active');
            $table->unsignedInteger('sessions_total')->nullable();
            $table->unsignedInteger('sessions_used')->default(0);
            $table->unsignedInteger('freeze_days_used')->default(0);
            $table->unsignedBigInteger('price_paise');
            $table->unsignedBigInteger('discount_paise')->default(0);
            $table->unsignedBigInteger('tax_paise')->default(0);
            $table->unsignedBigInteger('total_paise');
            $table->json('plan_snapshot');
            $table->json('allowed_branch_ids');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'member_id', 'status']);
            $table->index(['organization_id', 'starts_on', 'ends_on']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memberships');
    }
};
