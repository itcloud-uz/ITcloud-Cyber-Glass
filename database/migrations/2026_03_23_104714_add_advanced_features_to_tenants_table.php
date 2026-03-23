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
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('custom_domain')->nullable()->unique();
            $table->string('ssl_status')->default('not_set'); // not_set, pending, active, expired
            
            $table->string('referral_code')->nullable()->unique();
            $table->foreignId('referred_by_id')->nullable()->constrained('tenants')->onDelete('set null');
            
            $table->timestamp('last_active_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            //
        });
    }
};
