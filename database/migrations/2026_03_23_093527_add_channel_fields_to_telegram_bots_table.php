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
            $table->string('channel_type')->default('telegram'); // telegram, whatsapp, instagram
            $table->string('meta_verify_token')->nullable(); // For Meta Webhook verification
            $table->string('phone_number_id')->nullable(); // For WhatsApp
            $table->string('instagram_account_id')->nullable(); // For Instagram Direct
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('telegram_bots', function (Blueprint $table) {
            //
        });
    }
};
