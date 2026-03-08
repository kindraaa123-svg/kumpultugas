<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AcademicYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('academic_year')->insert([
            [
                'academic_year_id' => 1, 
                'name' => '2025/2026', 
                'start_date' => '2025-07-14', 
                'end_date' => '2026-07-10', 
                'is_active' => 1, 
                'created_at' => '2026-03-04 06:14:56', 
                'updated_at' => '2026-03-04 06:14:56'
            ]
        ]);
    }
}
