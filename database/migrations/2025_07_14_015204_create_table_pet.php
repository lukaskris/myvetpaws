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
        // Schema::table('pets', function (Blueprint $table) {
        //     $table->foreignId('species_id')->nullable()->after('breed')->constrained('species')->nullOnDelete();
        //     $table->foreignId('breed_id')->nullable()->after('species_id')->constrained('breeds')->nullOnDelete();
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::table('pets', function (Blueprint $table) {
        //     $table->dropColumn('species_id');
        //     $table->dropColumn('breed_id');
        // });
    }
};