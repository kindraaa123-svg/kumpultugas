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
        Schema::table('schedule', function (Blueprint $table) {
            $table->unsignedInteger('block_id')->after('academic_year_id')->nullable();
            $table->tinyInteger('session')->after('teacherid')->nullable(); // Sesi 1, 2, 3, 4, 5
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedule', function (Blueprint $table) {
            $table->dropColumn(['block_id', 'session']);
        });
    }
};
