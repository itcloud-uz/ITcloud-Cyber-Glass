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
        Schema::table('templates', function (Blueprint $table) {
            $table->string('service_type')->default('software'); // software, service, hybrid
            $table->json('includes')->nullable(); // JSON list of features included in price
            $table->json('extra_services')->nullable(); // JSON list of available sub-services
            $table->text('advantages')->nullable(); // Key advantages
            $table->string('payment_type')->default('one-time'); // monthly, yearly, one-time
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('templates', function (Blueprint $table) {
            //
        });
    }
};
