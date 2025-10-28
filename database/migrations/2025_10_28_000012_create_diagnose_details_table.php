<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diagnose_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('diagnose_id')->constrained('diagnoses')->cascadeOnDelete();
            $table->string('name');
            $table->enum('type', ['Primary', 'Differential'])->default('Primary');
            $table->enum('prognose', ['Fausta', 'Dubius', 'Infausta'])->default('Fausta');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diagnose_details');
    }
};

