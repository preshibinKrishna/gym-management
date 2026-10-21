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
        Schema::create('guardian_relationships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guardian_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('relationship');
            $table->boolean('can_view_profile')->default(true);
            $table->boolean('can_pay')->default(true);
            $table->timestamp('revoked_at')->nullable();
            $table->timestamp('review_required_at')->nullable();
            $table->timestamps();

            $table->unique(['member_id', 'guardian_user_id']);
            $table->index(['organization_id', 'guardian_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guardian_relationships');
    }
};
