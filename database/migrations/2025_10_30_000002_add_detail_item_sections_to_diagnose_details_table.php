<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('diagnose_details', function (Blueprint $table) {
            if (! Schema::hasColumn('diagnose_details', 'detail_item_sections')) {
                $table->string('detail_item_sections', 20)
                    ->default('diagnose')
                    ->after('diagnosis_master_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('diagnose_details', function (Blueprint $table) {
            if (Schema::hasColumn('diagnose_details', 'detail_item_sections')) {
                $table->dropColumn('detail_item_sections');
            }
        });
    }
};

