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
        Schema::table('assignment', function (Blueprint $table) {
            if (!Schema::hasColumn('assignment', 'grading_mode')) {
                $table->string('grading_mode', 10)->default('manual')->after('description');
            }
            if (!Schema::hasColumn('assignment', 'auto_score')) {
                $table->unsignedInteger('auto_score')->nullable()->after('grading_mode');
            }
        });

        Schema::table('quest', function (Blueprint $table) {
            $table->text('file')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assignment', function (Blueprint $table) {
            if (Schema::hasColumn('assignment', 'auto_score')) {
                $table->dropColumn('auto_score');
            }
            if (Schema::hasColumn('assignment', 'grading_mode')) {
                $table->dropColumn('grading_mode');
            }
        });

        Schema::table('quest', function (Blueprint $table) {
            $table->string('file', 255)->change();
        });
    }
};
