<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('click_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('click_trans_id')->unique();
            $table->string('merchant_trans_id'); // Local ID (e.g. academy_payments.id)
            $table->string('click_paydoc_id')->nullable();
            $table->decimal('amount', 15, 2);
            $table->integer('action'); // 0: Prepare, 1: Complete
            $table->integer('error')->default(0);
            $table->string('error_note')->nullable();
            $table->enum('status', ['pending', 'preparing', 'completed', 'canceled', 'failed'])->default('pending');
            $table->string('sign_time')->nullable();
            $table->string('sign_string')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('click_transactions');
    }
};
