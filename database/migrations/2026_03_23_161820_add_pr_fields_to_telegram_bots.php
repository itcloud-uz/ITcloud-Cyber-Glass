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
        Schema::table('telegram_bots', function (Blueprint $table) {
            if (!Schema::hasColumn('telegram_bots', 'channel_id')) {
                $table->string('channel_id')->nullable();
                $table->string('schedule_time')->nullable()->default('09:00');
                $table->string('theme')->nullable()->default('cyberpunk');
                $table->text('custom_prompt')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('telegram_bots', function (Blueprint $table) {
            if (Schema::hasColumn('telegram_bots', 'channel_id')) {
                $table->dropColumn(['channel_id', 'schedule_time', 'theme', 'custom_prompt']);
            }
        });
    }
};
