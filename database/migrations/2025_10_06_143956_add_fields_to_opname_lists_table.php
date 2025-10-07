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
        Schema::table('opname_lists', function (Blueprint $table) {
            $table->date('date')->nullable()->after('id');
            $table->unsignedBigInteger('customer_id')->nullable()->after('date');
            $table->text('medical_notes')->nullable()->after('price');
            
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opname_lists', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn(['date', 'customer_id', 'medical_notes']);
        });
    }
};
