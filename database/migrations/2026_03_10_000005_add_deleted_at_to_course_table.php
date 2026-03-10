<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('course')) {
            return;
        }

        Schema::table('course', function (Blueprint $table) {
            if (! Schema::hasColumn('course', 'deleted_at')) {
                $table->timestamp('deleted_at')->nullable()->after('coursename');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('course')) {
            return;
        }

        Schema::table('course', function (Blueprint $table) {
            if (Schema::hasColumn('course', 'deleted_at')) {
                $table->dropColumn('deleted_at');
            }
        });
    }
};
