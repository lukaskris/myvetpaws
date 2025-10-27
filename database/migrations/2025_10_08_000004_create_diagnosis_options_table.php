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
        Schema::create('diagnosis_options', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        DB::table('diagnosis_options')->insert([
            ['name' => 'Alergi', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Blood Parasitic', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Clamidia', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Cystitis', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Ear mite', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Endoparasitic', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diagnosis_options');
    }
};
