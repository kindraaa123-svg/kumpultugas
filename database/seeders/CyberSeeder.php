<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\AcademicYear;
use App\Models\Block;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\Employer;
use App\Models\Level;
use App\Models\Role;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CyberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AcademicYear::create(['name' => '2025/2026', 'start_date' => '2025-07-14', 'end_date' => '2026-07-10', 'is_active' => 1]);
        
        Block::create(['academic_year_id' => 1, 'name' => 'Block 6', 'order_no' => 1, 'day_of_week' => 1, 'date_start' => '2026-02-12', 'date_end' => '2026-03-17']);

        Classroom::create(['classname' => 'RPL X']);
        Classroom::create(['classname' => 'RPL XI']);
        Classroom::create(['classname' => 'RPL XII']);
        Classroom::create(['classname' => 'AKL X']);

        Course::create(['coursename' => 'Matematika']);
        Course::create(['coursename' => 'Bahasa Indonesia']);
        Course::create(['coursename' => 'IPS']);
        Course::create(['coursename' => 'IPA']);
        Course::create(['coursename' => 'Bahasa Inggris']);
        Course::create(['coursename' => 'Agama Islam']);

        Level::create(['levelname' => 'Employer']);
        Level::create(['levelname' => 'Teacher']);
        Level::create(['levelname' => 'Student']);

        Role::create(['rolename' => 'Superadmin']);
        Role::create(['rolename' => 'Admin']);
        Role::create(['rolename' => 'Guru']);
        Role::create(['rolename' => 'Curiculum']);

        User::create(['username' => 'superadmin', 'password' => Hash::make('password'), 'levelid' => 1]);
        User::create(['username' => 'admin', 'password' => Hash::make('password'), 'levelid' => 1]);
        User::create(['username' => 'guru', 'password' => Hash::make('password'), 'levelid' => 2]);
        User::create(['username' => 'siswa', 'password' => Hash::make('password'), 'levelid' => 3]);
        User::create(['username' => 'curiculum', 'password' => Hash::make('password'), 'levelid' => 2]);

        Employer::create(['name' => 'superadmin', 'email' => 'superadmin@gmail.com', 'phonenumber' => '76543', 'roleid' => 1, 'userid' => 1]);
        Employer::create(['name' => 'admin', 'email' => 'admin@gmail.com', 'phonenumber' => '87644544', 'roleid' => 2, 'userid' => 2]);

        Teacher::create(['name' => 'guru', 'email' => 'guru@gmail.com', 'phonenumber' => '121334', 'roleid' => 3, 'userid' => 3]);
        Teacher::create(['name' => 'curiculum', 'email' => 'curiculum@gmail.com', 'phonenumber' => '4546857654', 'roleid' => 5, 'userid' => 5]);

        Student::create(['name' => 'siswa', 'email' => 'siswa@gmail.com', 'phonenumber' => '76543', 'classid' => 1, 'userid' => 4]);

        Schedule::create(['academic_year_id' => 1, 'block_id' => 1, 'courseid' => 1, 'classid' => 1, 'teacherid' => 1, 'session' => 1]);
        Schedule::create(['academic_year_id' => 1, 'block_id' => 1, 'courseid' => 2, 'classid' => 1, 'teacherid' => 2, 'session' => 2]);
        Schedule::create(['academic_year_id' => 1, 'block_id' => 1, 'courseid' => 3, 'classid' => 2, 'teacherid' => 1, 'session' => 1]);
        Schedule::create(['academic_year_id' => 1, 'block_id' => 1, 'courseid' => 4, 'classid' => 2, 'teacherid' => 2, 'session' => 3]);
    }
}
