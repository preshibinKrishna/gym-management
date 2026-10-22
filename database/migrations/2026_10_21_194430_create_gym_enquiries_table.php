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
        Schema::create('gym_enquiries', function (Blueprint $table) {
            $table->id();
            $table->string('gym_name');
            $table->string('owner_name');
            $table->string('email')->index();
            $table->string('phone')->nullable();
            $table->string('city')->nullable();
            $table->string('source')->nullable();
            $table->date('follow_up_on')->nullable();
            $table->string('status')->default('new');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'follow_up_on']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gym_enquiries');
    }
};
