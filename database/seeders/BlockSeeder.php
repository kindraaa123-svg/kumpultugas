<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BlockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('block')->insert([
            [
                'block_id' => 1, 
                'academic_year_id' => 1, 
                'name' => 'Block 6', 
                'date_start' => '2026-02-12', 
                'date_end' => '2026-03-17', 
                'created_at' => '2026-03-04 06:14:56', 
                'updated_at' => '2026-03-04 06:14:56'
            ]
        ]);
    }
}
