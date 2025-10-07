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
        Schema::table('diagnoses', function (Blueprint $table) {
            // Hapus kolom yang tidak diperlukan
            $table->dropForeign(['detail_transaction_id']);
            $table->dropColumn('detail_transaction_id');
            
            // Tambahkan kolom yang diperlukan
            $table->string('name')->after('id');
            $table->enum('prognose', ['Fausta', 'Dubius', 'Infausta'])->default('Fausta')->after('name');
            $table->enum('type', ['Primary', 'Differential'])->default('Primary')->after('prognose');
            $table->foreignId('opname_list_id')->after('type')->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('diagnoses', function (Blueprint $table) {
            $table->dropForeign(['opname_list_id']);
            $table->dropColumn(['name', 'prognose', 'type', 'opname_list_id']);
            $table->unsignedBigInteger('detail_transaction_id');
            $table->foreign('detail_transaction_id')->references('id')->on('detail_transactions')->onDelete('cascade');
        });
    }
};
