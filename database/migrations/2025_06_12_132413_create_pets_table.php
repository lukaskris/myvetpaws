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
        Schema::create('pets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('owner_pet_id')->nullable()->constrained('owner_pets')->nullOnDelete();
            $table->string('name');
            $table->foreignId('species_id')->nullable()->constrained('species')->nullOnDelete();
            $table->foreignId('breed_id')->nullable()->constrained('breeds')->nullOnDelete();
            $table->string('species');
            $table->string('breed');
            $table->string('gender')->nullable();
            $table->date('birth_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pets');
    }
};
