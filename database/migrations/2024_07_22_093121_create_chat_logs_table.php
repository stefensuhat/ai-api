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
            $table->string('prompt')->nullable();
            $table->string('msg_id');
            $table->string('model');
            $table->json('content');
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
