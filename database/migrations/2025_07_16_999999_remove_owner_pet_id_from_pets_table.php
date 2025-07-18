<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pets', function (Blueprint $table) {
            $table->dropForeign(['owner_pet_id']);
            $table->dropColumn('owner_pet_id');
        });
    }

    public function down(): void
    {
        Schema::table('pets', function (Blueprint $table) {
            $table->foreignId('owner_pet_id')->nullable()->constrained('owner_pets')->nullOnDelete();
        });
    }
};