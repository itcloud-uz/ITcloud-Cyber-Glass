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
        Schema::table('ai_logs', function (Blueprint $table) {
            $table->string('chat_id')->nullable();
            $table->text('user_message')->nullable();
            $table->text('bot_response')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_logs', function (Blueprint $table) {
            //
        });
    }
};
