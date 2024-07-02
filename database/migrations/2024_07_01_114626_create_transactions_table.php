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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('order_id');
            $table->foreignUlid('user_id')->constrained('users');
            $table->foreignUlid('pricing_plan_id')->constrained('pricing_plans');
            $table->decimal('subtotal', 14);
            $table->decimal('discount', 14);
            $table->decimal('grand_total', 14);
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
