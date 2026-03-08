<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('class')->insert([
            ['classid' => 1, 'classname' => 'RPL X'],
            ['classid' => 2, 'classname' => 'RPL XI'],
            ['classid' => 3, 'classname' => 'RPL XII'],
            ['classid' => 4, 'classname' => 'AKL X'],
        ]);
    }
}
