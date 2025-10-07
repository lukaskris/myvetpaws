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
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('opname_list_id');
            $table->unsignedBigInteger('product_id');
            $table->float('amount');
            $table->string('frequency');
            $table->integer('duration');
            $table->float('total_amount');
            $table->timestamps();

            $table->foreign('opname_list_id')->references('id')->on('opname_lists')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};
