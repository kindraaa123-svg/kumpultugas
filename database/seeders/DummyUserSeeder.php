<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class DummyUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');
        $password = Hash::make('password123'); // Default password for all dummy users

        // 1. Create 4 Students (Level 3)
        for ($i = 0; $i < 4; $i++) {
            $username = $faker->unique()->userName;
            $userId = DB::table('user')->insertGetId([
                'username' => $username,
                'password' => $password,
                'levelid' => 3,
            ]);

            DB::table('student')->insert([
                'name' => $faker->name,
                'email' => $faker->unique()->email,
                'phonenumber' => $faker->phoneNumber,
                'classid' => $faker->numberBetween(1, 5), // Assuming class IDs 1-5 exist
                'userid' => $userId,
            ]);
        }

        // 2. Create 4 Teachers (Level 2)
        for ($i = 0; $i < 4; $i++) {
            $username = $faker->unique()->userName;
            $userId = DB::table('user')->insertGetId([
                'username' => $username,
                'password' => $password,
                'levelid' => 2,
            ]);

            DB::table('teacher')->insert([
                'name' => $faker->name,
                'email' => $faker->unique()->email,
                'phonenumber' => $faker->phoneNumber,
                'roleid' => 3, // Assuming Role ID 3 is Guru
                'userid' => $userId,
            ]);
        }

        // 3. Create 2 Employers/Admins (Level 1)
        for ($i = 0; $i < 2; $i++) {
            $username = $faker->unique()->userName;
            $userId = DB::table('user')->insertGetId([
                'username' => $username,
                'password' => $password,
                'levelid' => 1,
            ]);

            DB::table('employer')->insert([
                'name' => $faker->name,
                'email' => $faker->unique()->email,
                'phonenumber' => $faker->phoneNumber,
                'roleid' => 2, // Assuming Role ID 2 is Admin
                'userid' => $userId,
            ]);
        }
    }
}
