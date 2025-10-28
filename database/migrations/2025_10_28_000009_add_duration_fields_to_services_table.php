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
        Schema::table('services', function (Blueprint $table) {
            if (!Schema::hasColumn('services', 'duration')) {
                $table->unsignedInteger('duration')->default(0)->after('price');
            }
            if (!Schema::hasColumn('services', 'duration_type')) {
                $table->string('duration_type')->default('minutes')->after('duration');
            }
            if (!Schema::hasColumn('services', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('duration_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            if (Schema::hasColumn('services', 'is_active')) {
                $table->dropColumn('is_active');
            }
            if (Schema::hasColumn('services', 'duration_type')) {
                $table->dropColumn('duration_type');
            }
            if (Schema::hasColumn('services', 'duration')) {
                $table->dropColumn('duration');
            }
        });
    }
};

