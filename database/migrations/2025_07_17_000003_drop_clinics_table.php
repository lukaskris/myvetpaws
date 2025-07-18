<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('clinics');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // You may want to recreate the clinics table here if needed
    }
};