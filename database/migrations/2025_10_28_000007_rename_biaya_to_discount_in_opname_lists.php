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
            if (!Schema::hasColumn('opname_lists', 'discount')) {
                $table->decimal('discount', 10, 2)->default(0)->after('description');
            }
        });

        if (Schema::hasColumn('opname_lists', 'biaya')) {
            DB::table('opname_lists')->update(['discount' => DB::raw('COALESCE(biaya, 0)')]);
        }

        Schema::table('opname_lists', function (Blueprint $table) {
            if (Schema::hasColumn('opname_lists', 'biaya')) {
                $table->dropColumn('biaya');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opname_lists', function (Blueprint $table) {
            if (!Schema::hasColumn('opname_lists', 'biaya')) {
                $table->decimal('biaya', 10, 2)->default(0)->after('description');
            }
        });

        if (Schema::hasColumn('opname_lists', 'discount')) {
            DB::table('opname_lists')->update(['biaya' => DB::raw('COALESCE(discount, 0)')]);
        }

        Schema::table('opname_lists', function (Blueprint $table) {
            if (Schema::hasColumn('opname_lists', 'discount')) {
                $table->dropColumn('discount');
            }
        });
    }
};

