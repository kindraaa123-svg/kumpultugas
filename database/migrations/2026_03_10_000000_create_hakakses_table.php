<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hakakses', function (Blueprint $table) {
            $table->id();
            $table->string('group_key', 50);
            $table->string('menu_key', 50);
            $table->boolean('allowed')->default(false);
            $table->timestamps();

            $table->unique(['group_key', 'menu_key']);
        });

        $now = now();
        $defaults = [
            'superadmin' => ['tugas' => 1, 'jadwal' => 1, 'userdata' => 1, 'data' => 1],
            'admin' => ['tugas' => 1, 'jadwal' => 1, 'userdata' => 1, 'data' => 1],
            'curiculum' => ['tugas' => 1, 'jadwal' => 1, 'userdata' => 0, 'data' => 1],
            'guru' => ['tugas' => 1, 'jadwal' => 1, 'userdata' => 0, 'data' => 0],
            'siswa' => ['tugas' => 1, 'jadwal' => 1, 'userdata' => 0, 'data' => 0],
        ];

        $rows = [];
        foreach ($defaults as $groupKey => $menus) {
            foreach ($menus as $menuKey => $allowed) {
                $rows[] = [
                    'group_key' => $groupKey,
                    'menu_key' => $menuKey,
                    'allowed' => (bool) $allowed,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('hakakses')->insert($rows);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hakakses');
    }
};
