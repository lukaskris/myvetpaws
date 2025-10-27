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
            if (! Schema::hasColumn('opname_list_pet', 'opname_list_id')) {
                $table->foreignId('opname_list_id')
                    ->after('id')
                    ->constrained('opname_lists')
                    ->cascadeOnDelete();
            }

            if (! Schema::hasColumn('opname_list_pet', 'pet_id')) {
                $table->foreignId('pet_id')
                    ->after('opname_list_id')
                    ->constrained('pets')
                    ->cascadeOnDelete();
            }

            if (! Schema::hasColumn('opname_list_pet', 'medical_notes')) {
                $table->text('medical_notes')->nullable()->after('pet_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opname_list_pet', function (Blueprint $table) {
            if (Schema::hasColumn('opname_list_pet', 'medical_notes')) {
                $table->dropColumn('medical_notes');
            }

            if (Schema::hasColumn('opname_list_pet', 'pet_id')) {
                $table->dropForeign(['pet_id']);
                $table->dropColumn('pet_id');
            }

            if (Schema::hasColumn('opname_list_pet', 'opname_list_id')) {
                $table->dropForeign(['opname_list_id']);
                $table->dropColumn('opname_list_id');
            }
        });
    }
};
