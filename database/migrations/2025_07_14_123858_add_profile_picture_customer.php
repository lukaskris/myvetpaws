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
        //
        Schema::table('customers', function (Blueprint $table) {
            $table->string('profile_picture')->nullable()->after('name');
            $table->string('title')->nullable()->after('profile_picture');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('profile_picture');
            $table->dropColumn('title');
        });
    }
};
