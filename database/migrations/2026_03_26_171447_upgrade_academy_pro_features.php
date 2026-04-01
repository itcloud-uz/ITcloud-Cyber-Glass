<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add extra fields to applications (and students)
        Schema::table('academy_applications', function (Blueprint $table) {
            if (!Schema::hasColumn('academy_applications', 'passport_series')) {
                $table->string('passport_series', 2)->nullable();
                $table->string('passport_number', 7)->nullable();
                $table->string('jshir', 14)->nullable(); // PINFL
                $table->text('address')->nullable();
            }
        });

        // 2. Course Pricing
        Schema::table('academy_courses', function (Blueprint $table) {
            if (!Schema::hasColumn('academy_courses', 'price')) {
                $table->decimal('price', 15, 2)->default(0); // Total price
                $table->decimal('monthly_fee', 15, 2)->default(0); // Monthly fee
            }
        });

        // 3. Payments Table
        if (!Schema::hasTable('academy_payments')) {
            Schema::create('academy_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('course_id')->constrained('academy_courses')->cascadeOnDelete();
                $table->decimal('amount', 15, 2);
                $table->string('payment_method')->default('cash'); // click, payme, cash
                $table->text('details')->nullable();
                $table->string('status')->default('completed'); // pending, completed, failed
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('academy_payments');
        Schema::table('academy_courses', function (Blueprint $table) {
            $table->dropColumn(['price', 'monthly_fee']);
        });
        Schema::table('academy_applications', function (Blueprint $table) {
            $table->dropColumn(['passport_series', 'passport_number', 'jshir', 'address']);
        });
    }
};
