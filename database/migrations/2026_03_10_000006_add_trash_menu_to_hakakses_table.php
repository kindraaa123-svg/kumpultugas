<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('hakakses')) {
            return;
        }

        $now = now();
        $groupDefaults = [
            'superadmin' => true,
            'admin' => true,
            'curiculum' => false,
            'guru' => false,
            'siswa' => false,
        ];

        $rows = [];
        foreach ($groupDefaults as $groupKey => $allowed) {
            $rows[] = [
                'group_key' => $groupKey,
                'menu_key' => 'trash',
                'allowed' => (bool) $allowed,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('hakakses')->insertOrIgnore($rows);
    }

    public function down(): void
    {
        if (! Schema::hasTable('hakakses')) {
            return;
        }

        DB::table('hakakses')->where('menu_key', 'trash')->delete();
    }
};
