<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Schedule;
use App\Models\Course;
use Carbon\Carbon;
use Faker\Factory as Faker;

class DummyAssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // 1. Get Schedules for Block 6 (ID 1) where Teacher Role is 3
        // We need to join or filter manually since roleid is in teacher table
        $schedules = Schedule::with(['teacher', 'course'])
            ->where('block_id', 1) // Assuming Block 6 has ID 1 based on previous checks
            ->get()
            ->filter(function ($schedule) {
                return $schedule->teacher && $schedule->teacher->roleid == 3;
            });

        if ($schedules->isEmpty()) {
            $this->command->info('No schedules found for Block 6 with Teacher Role 3. Seeding aborted.');
            return;
        }

        // 2. Create 10 Assignments
        $count = 0;
        $scheduleList = $schedules->values(); // Reset keys for random picking
        
        while ($count < 10) {
            $schedule = $scheduleList->random();
            $courseName = $schedule->course ? $schedule->course->coursename : 'Umum';

            // Generate sensible assignment data
            $topics = [
                'Matematika' => ['Aljabar Linear', 'Kalkulus Dasar', 'Geometri', 'Statistika', 'Trigonometri'],
                'Fisika' => ['Hukum Newton', 'Termodinamika', 'Gelombang', 'Listrik Statis', 'Optik'],
                'Biologi' => ['Sel Hewan', 'Fotosintesis', 'Genetika', 'Ekosistem', 'Anatomi Manusia'],
                'Kimia' => ['Ikatan Kimia', 'Stoikiometri', 'Asam Basa', 'Termokimia', 'Laju Reaksi'],
                'Bahasa Indonesia' => ['Puisi', 'Cerpen', 'Teks Eksplanasi', 'Pidato', 'Debat'],
                'Bahasa Inggris' => ['Tenses', 'Narrative Text', 'Passive Voice', 'Report Text', 'Conversation'],
                'Sejarah' => ['Perang Dunia I', 'Revolusi Industri', 'Kerajaan Majapahit', 'Kemerdekaan RI', 'Perang Dingin'],
                'Geografi' => ['Peta dan Pemetaan', 'Atmosfer', 'Hidrosfer', 'Biosfer', 'Mitigasi Bencana'],
                'Ekonomi' => ['Permintaan dan Penawaran', 'Pasar Modal', 'Akuntansi Dasar', 'Pajak', 'Inflasi'],
                'Sosiologi' => ['Interaksi Sosial', 'Konflik Sosial', 'Perubahan Sosial', 'Lembaga Sosial', 'Penelitian Sosial'],
                'Default' => ['Latihan Soal', 'Tugas Harian', 'Proyek Kelompok', 'Presentasi', 'Review Materi']
            ];

            // Try to match course name to topic, otherwise use Default
            $matchedKey = 'Default';
            foreach ($topics as $key => $values) {
                if (stripos($courseName, $key) !== false) {
                    $matchedKey = $key;
                    break;
                }
            }
            
            $topic = $faker->randomElement($topics[$matchedKey]);
            $title = "Tugas $courseName: $topic";
            
            // Random date within Block 6 range (2026-02-12 to 2026-03-17)
            // Or slightly dynamic based on "today" (2026-03-10)
            $startDate = Carbon::create(2026, 2, 12)->addDays(rand(0, 30)); // Random start date
            $endDate = (clone $startDate)->addDays(rand(3, 7)); // Due date 3-7 days later

            DB::table('assignment')->insert([
                'scheduleid' => $schedule->scheduleid,
                'name' => $title,
                'description' => "Silakan kerjakan tugas mengenai $topic. Baca materi pada buku cetak halaman " . rand(10, 200) . "-" . rand(201, 300) . ". Kumpulkan dalam format PDF sebelum tenggat waktu.",
                'grading_mode' => 'manual', // Default grading mode
                'auto_score' => null,
                'time_start' => $startDate,
                'time_end' => $endDate,
                'created_at' => now(),
            ]);

            $count++;
        }
    }
}
