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
        Schema::table('chat_groups', function (Blueprint $table) {
            $table->foreignUlid('prompt_id')->nullable()->after('id');
        });
        Schema::table('chats', function (Blueprint $table) {
            $table->string('tone')->nullable()->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_groups', function (Blueprint $table) {
            $table->dropColumn(['prompt_id']);
        });

        Schema::table('chat_groups', function (Blueprint $table) {
            $table->dropColumn(['tone']);
        });
    }
};
