<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ruang_mapel')) {
            Schema::create('ruang_mapel', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('scheduleid');
                $table->unsignedInteger('teacherid')->nullable();
                $table->timestamps();

                $table->unique(['scheduleid']);
            });
        }

        $now = now();

        $rows = DB::table('assignment')
            ->join('schedule', 'schedule.scheduleid', '=', 'assignment.scheduleid')
            ->select('assignment.scheduleid', 'schedule.teacherid')
            ->distinct()
            ->get()
            ->map(function ($r) use ($now) {
                return [
                    'scheduleid' => $r->scheduleid,
                    'teacherid' => $r->teacherid,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })
            ->all();

        if (! empty($rows)) {
            DB::table('ruang_mapel')->insertOrIgnore($rows);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ruang_mapel');
    }
};
