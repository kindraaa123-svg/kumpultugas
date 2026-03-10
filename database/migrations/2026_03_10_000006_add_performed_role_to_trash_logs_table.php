<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('trash_logs')) {
            return;
        }

        Schema::table('trash_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('trash_logs', 'performed_role')) {
                $table->string('performed_role', 50)->nullable()->after('performed_username');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('trash_logs') || ! Schema::hasColumn('trash_logs', 'performed_role')) {
            return;
        }

        Schema::table('trash_logs', function (Blueprint $table) {
            $table->dropColumn('performed_role');
        });
    }
};
