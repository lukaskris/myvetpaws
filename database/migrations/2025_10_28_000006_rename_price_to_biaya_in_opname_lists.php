<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('opname_lists', function (Blueprint $table) {
            if (!Schema::hasColumn('opname_lists', 'biaya')) {
                $table->decimal('biaya', 10, 2)->default(0)->after('description');
            }
        });

        // Copy existing values from price to biaya if price exists
        if (Schema::hasColumn('opname_lists', 'price')) {
            DB::table('opname_lists')->update(['biaya' => DB::raw('COALESCE(price, 0)')]);
        }

        // Drop old column if exists
        Schema::table('opname_lists', function (Blueprint $table) {
            if (Schema::hasColumn('opname_lists', 'price')) {
                $table->dropColumn('price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opname_lists', function (Blueprint $table) {
            if (!Schema::hasColumn('opname_lists', 'price')) {
                $table->integer('price')->default(0)->after('description');
            }
        });

        if (Schema::hasColumn('opname_lists', 'biaya')) {
            DB::table('opname_lists')->update(['price' => DB::raw('CAST(COALESCE(biaya, 0) AS INTEGER)')]);
        }

        Schema::table('opname_lists', function (Blueprint $table) {
            if (Schema::hasColumn('opname_lists', 'biaya')) {
                $table->dropColumn('biaya');
            }
        });
    }
};

