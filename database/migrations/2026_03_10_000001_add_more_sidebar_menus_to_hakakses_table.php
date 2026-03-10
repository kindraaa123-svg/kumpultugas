<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $menus = [
            'tugas',
            'jadwal',
            'userdata',
            'data',
            'data_course',
            'data_class',
            'data_academicyear',
            'data_block',
            'data_nilai',
            'activity_log',
            'backup_database',
            'hak_akses',
            'pengaturan',
        ];

        $defaults = [
            'superadmin' => [
                'tugas' => 1,
                'jadwal' => 1,
                'userdata' => 1,
                'data' => 1,
                'data_course' => 1,
                'data_class' => 1,
                'data_academicyear' => 1,
                'data_block' => 1,
                'data_nilai' => 1,
                'activity_log' => 1,
                'backup_database' => 1,
                'hak_akses' => 1,
                'pengaturan' => 1,
            ],
            'admin' => [
                'tugas' => 1,
                'jadwal' => 1,
                'userdata' => 1,
                'data' => 1,
                'data_course' => 1,
                'data_class' => 1,
                'data_academicyear' => 1,
                'data_block' => 1,
                'data_nilai' => 0,
                'activity_log' => 1,
                'backup_database' => 1,
                'hak_akses' => 0,
                'pengaturan' => 1,
            ],
            'curiculum' => [
                'tugas' => 1,
                'jadwal' => 1,
                'userdata' => 0,
                'data' => 1,
                'data_course' => 1,
                'data_class' => 1,
                'data_academicyear' => 1,
                'data_block' => 1,
                'data_nilai' => 1,
                'activity_log' => 0,
                'backup_database' => 0,
                'hak_akses' => 0,
                'pengaturan' => 0,
            ],
            'guru' => [
                'tugas' => 1,
                'jadwal' => 1,
                'userdata' => 0,
                'data' => 0,
                'data_course' => 0,
                'data_class' => 0,
                'data_academicyear' => 0,
                'data_block' => 0,
                'data_nilai' => 1,
                'activity_log' => 0,
                'backup_database' => 0,
                'hak_akses' => 0,
                'pengaturan' => 0,
            ],
            'siswa' => [
                'tugas' => 1,
                'jadwal' => 1,
                'userdata' => 0,
                'data' => 0,
                'data_course' => 0,
                'data_class' => 0,
                'data_academicyear' => 0,
                'data_block' => 0,
                'data_nilai' => 0,
                'activity_log' => 0,
                'backup_database' => 0,
                'hak_akses' => 0,
                'pengaturan' => 0,
            ],
        ];

        $rows = [];
        foreach ($defaults as $groupKey => $groupMenus) {
            foreach ($menus as $menuKey) {
                $rows[] = [
                    'group_key' => $groupKey,
                    'menu_key' => $menuKey,
                    'allowed' => ! empty($groupMenus[$menuKey]),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('hakakses')->insertOrIgnore($rows);
    }

    public function down(): void
    {
        DB::table('hakakses')
            ->whereIn('menu_key', [
                'data_course',
                'data_class',
                'data_academicyear',
                'data_block',
                'data_nilai',
                'activity_log',
                'backup_database',
                'hak_akses',
                'pengaturan',
            ])
            ->delete();
    }
};
