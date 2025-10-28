<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diagnosis_masters', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::table('diagnose_details', function (Blueprint $table) {
            if (! Schema::hasColumn('diagnose_details', 'diagnosis_master_id')) {
                $table->foreignId('diagnosis_master_id')
                    ->nullable()
                    ->after('diagnose_id')
                    ->constrained('diagnosis_masters')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('diagnose_details', 'notes')) {
                $table->text('notes')->nullable()->after('prognose');
            }
        });
    }

    public function down(): void
    {
        Schema::table('diagnose_details', function (Blueprint $table) {
            if (Schema::hasColumn('diagnose_details', 'diagnosis_master_id')) {
                $table->dropForeign(['diagnosis_master_id']);
                $table->dropColumn('diagnosis_master_id');
            }

            if (Schema::hasColumn('diagnose_details', 'notes')) {
                $table->dropColumn('notes');
            }
        });

        Schema::dropIfExists('diagnosis_masters');
    }
};
