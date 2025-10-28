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
        Schema::table('opname_list_pet', function (Blueprint $table) {
            if (!Schema::hasColumn('opname_list_pet', 'duration_days')) {
                $table->unsignedTinyInteger('duration_days')->default(0)->after('medical_notes');
            }
            if (!Schema::hasColumn('opname_list_pet', 'is_done')) {
                $table->boolean('is_done')->default(false)->after('duration_days');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opname_list_pet', function (Blueprint $table) {
            if (Schema::hasColumn('opname_list_pet', 'is_done')) {
                $table->dropColumn('is_done');
            }
            if (Schema::hasColumn('opname_list_pet', 'duration_days')) {
                $table->dropColumn('duration_days');
            }
        });
    }
};

