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
        Schema::create('chat_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id');
            $table->foreignUlid('user_id');
            $table->string('msg_id');
            $table->string('model');
            $table->string('stop_reason');
            $table->string('stop_sequence');
            $table->unsignedInteger('input_tokens');
            $table->unsignedInteger('output_tokens');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_logs');
    }
};
