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
        $tables = [
            'customers',
            'pets',
            'services',
            'subscriptions',
            'medicines',
            'medical_usage_logs',
            'medical_records',
        ];
        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropForeign(['clinic_id']);
                $table->dropColumn('clinic_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
        });
        Schema::table('pets', function (Blueprint $table) {
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
        });
        Schema::table('services', function (Blueprint $table) {
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
        });
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
        });
        Schema::table('medicines', function (Blueprint $table) {
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
        });
        Schema::table('medical_usage_logs', function (Blueprint $table) {
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
        });
        Schema::table('medical_records', function (Blueprint $table) {
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
        });
    }
};