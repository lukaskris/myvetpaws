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
        Schema::create('statistics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('detail_transaction_id');
            $table->integer('weight');
            $table->integer('temperature');
            $table->integer('respiration');
            $table->integer('pulsus');
            $table->timestamps();
            $table->foreign('detail_transaction_id')->references('id')->on('detail_transactions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('statistics');
    }
};
