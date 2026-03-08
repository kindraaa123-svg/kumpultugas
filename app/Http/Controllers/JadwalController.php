<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Block;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\Teacher;
use App\Models\Schedule;
use App\Models\Jadwal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JadwalController extends Controller
{
    public function index()
    {
        $system = DB::table('system')->first();
        $activeYear = AcademicYear::where('is_active', 1)->first();
        
        // Find the active block. Priority: User selected (is_active=1) -> Date based -> First available
        $activeBlock = null;
        if ($activeYear) {
            // 1. Check for manually set active block
            $activeBlock = Block::where('academic_year_id', $activeYear->academic_year_id)
                ->where('is_active', 1)
                ->first();

            // 2. If no manually active block, check date
            if (!$activeBlock) {
                $today = date('Y-m-d');
                $activeBlock = Block::where('academic_year_id', $activeYear->academic_year_id)
                    ->where('date_start', '<=', $today)
                    ->where('date_end', '>=', $today)
                    ->first();
            }
            
            // 3. Fallback to first block
            if (!$activeBlock) {
                $activeBlock = Block::where('academic_year_id', $activeYear->academic_year_id)->first();
            }
        }

        $classes = DB::table('class')->get(); 
        $courses = DB::table('course')->get();
        // Hanya ambil guru dengan roleid 3 (Guru) di tabel teacher, dan levelid 2 (Teacher) di tabel user
        $teachers = DB::table('teacher')
            ->join('user', 'teacher.userid', '=', 'user.userid')
            ->where('user.levelid', 2)
            ->where('teacher.roleid', 3)
            ->select('teacher.*')
            ->get();
        
        $years = DB::table('academic_year')->get(); 
        $blocks = DB::table('block')->get(); 

        $schedules = [];
        
        if ($activeBlock) {
            $rawSchedules = Schedule::with(['course', 'teacher'])
                ->where('academic_year_id', $activeYear->academic_year_id)
                ->where('block_id', $activeBlock->block_id)
                ->get();
            
            foreach ($rawSchedules as $s) {
                $schedules[$s->classid][$s->session] = $s;
            }
        }

        echo view('all.header', compact('system'));
        echo view('all.menu', compact('system'));
        echo view('cyber.jadwal.index', compact('activeYear', 'activeBlock', 'classes', 'schedules', 'courses', 'teachers', 'years', 'blocks'));
        echo view('all.footer');
    }

    public function setting()
    {
        $system = DB::table('system')->first();
        $years = AcademicYear::all();
        $blocks = Block::all();
        $activeYear = AcademicYear::where('is_active', 1)->first();

        echo view('all.header', compact('system'));
        echo view('all.menu', compact('system'));
        echo view('cyber.jadwal.setting', compact('years', 'blocks', 'activeYear'));
        echo view('all.footer');
    }

    public function updateSetting(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required',
            'block_id' => 'required',
        ]);

        // Reset all active years and blocks
        AcademicYear::where('is_active', 1)->update(['is_active' => 0]);
        Block::where('is_active', 1)->update(['is_active' => 0]);
        
        // Set new active year and block
        AcademicYear::where('academic_year_id', $request->academic_year_id)->update(['is_active' => 1]);
        Block::where('block_id', $request->block_id)->update(['is_active' => 1]);

        return redirect()->route('jadwal.index')->with('success', 'Settings updated');
    }

    public function editSchedule($classid, $session)
    {
        $system = DB::table('system')->first();
        $activeYear = AcademicYear::where('is_active', 1)->first();
        $today = date('Y-m-d');
        $activeBlock = Block::where('academic_year_id', $activeYear->academic_year_id)
            ->where('date_start', '<=', $today)
            ->where('date_end', '>=', $today)
            ->first();
        
        if (!$activeBlock) {
            $activeBlock = Block::where('academic_year_id', $activeYear->academic_year_id)->first();
        }

        $classroom = Classroom::find($classid);
        $courses = Course::all();
        $teachers = Teacher::all();
        
        $schedule = Schedule::where('academic_year_id', $activeYear->academic_year_id)
            ->where('block_id', $activeBlock->block_id)
            ->where('classid', $classid)
            ->where('session', $session)
            ->first();

        echo view('all.header', compact('system'));
        echo view('all.menu', compact('system'));
        echo view('cyber.jadwal.edit', compact('classroom', 'session', 'courses', 'teachers', 'schedule', 'activeYear', 'activeBlock'));
        echo view('all.footer');
    }

    public function updateSchedule(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required',
            'block_id' => 'required',
            'classid' => 'required',
            'session' => 'required',
            'courseid' => 'required',
            'teacherid' => 'required',
        ]);

        // Check if schedule exists
        $existing = DB::table('schedule')
            ->where('academic_year_id', $request->academic_year_id)
            ->where('block_id', $request->block_id)
            ->where('classid', $request->classid)
            ->where('session', $request->session)
            ->first();

        if ($existing) {
            DB::table('schedule')
                ->where('scheduleid', $existing->scheduleid)
                ->update([
                    'courseid' => $request->courseid,
                    'teacherid' => $request->teacherid,
                    'updated_at' => now(),
                ]);
        } else {
            DB::table('schedule')->insert([
                'academic_year_id' => $request->academic_year_id,
                'block_id' => $request->block_id,
                'classid' => $request->classid,
                'session' => $request->session,
                'courseid' => $request->courseid,
                'teacherid' => $request->teacherid,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()->route('jadwal.index')->with('success', 'Schedule updated');
    }
}
