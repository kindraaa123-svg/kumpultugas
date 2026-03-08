<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('course')->insert([
            ['courseid' => 1, 'coursename' => 'Matematika'],
            ['courseid' => 2, 'coursename' => 'Bahasa Indonesia'],
            ['courseid' => 3, 'coursename' => 'IPS'],
            ['courseid' => 4, 'coursename' => 'IPA'],
            ['courseid' => 5, 'coursename' => 'Bahasa Inggris'],
            ['courseid' => 6, 'coursename' => 'Agama Islam'],
        ]);
    }
}
