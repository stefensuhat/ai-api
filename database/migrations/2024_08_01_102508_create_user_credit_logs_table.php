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
        Schema::create('user_credit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_credit_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('loggable_id');
            $table->string('loggable_type')->nullable();
            $table->string('description')->nullable();
            $table->decimal('amount', 14)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_credit_logs');
    }
};
