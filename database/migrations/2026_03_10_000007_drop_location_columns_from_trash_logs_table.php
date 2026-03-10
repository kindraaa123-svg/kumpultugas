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
            $dropColumns = [];
            if (Schema::hasColumn('trash_logs', 'performed_longitude')) {
                $dropColumns[] = 'performed_longitude';
            }
            if (Schema::hasColumn('trash_logs', 'performed_latitude')) {
                $dropColumns[] = 'performed_latitude';
            }

            if (! empty($dropColumns)) {
                $table->dropColumn($dropColumns);
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('trash_logs')) {
            return;
        }

        Schema::table('trash_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('trash_logs', 'performed_longitude')) {
                $table->decimal('performed_longitude', 11, 8)->nullable()->after('performed_ip');
            }
            if (! Schema::hasColumn('trash_logs', 'performed_latitude')) {
                $table->decimal('performed_latitude', 10, 8)->nullable()->after('performed_longitude');
            }
        });
    }
};
