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
        
        // Find the current block based on today's date or just the first one if not found
        $today = date('Y-m-d');
        $activeBlock = null;
        if ($activeYear) {
            $activeBlock = Block::where('academic_year_id', $activeYear->academic_year_id)
                ->where('date_start', '<=', $today)
                ->where('date_end', '>=', $today)
                ->first();
            
            if (!$activeBlock) {
                $activeBlock = Block::where('academic_year_id', $activeYear->academic_year_id)->first();
            }
        }

        $classes = Classroom::all();
        $courses = Course::all();
        $teachers = DB::table('teacher')->where('roleid','3')->get();
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
        echo view('cyber.jadwal.index', compact('activeYear', 'activeBlock', 'classes', 'schedules', 'courses', 'teachers'));
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

        // Reset all active years
        AcademicYear::where('is_active', 1)->update(['is_active' => 0]);
        
        // Set new active year
        AcademicYear::where('academic_year_id', $request->academic_year_id)->update(['is_active' => 1]);

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

        Schedule::updateOrCreate(
            [
                'academic_year_id' => $request->academic_year_id,
                'block_id' => $request->block_id,
                'classid' => $request->classid,
                'session' => $request->session,
            ],
            [
                'courseid' => $request->courseid,
                'teacherid' => $request->teacherid,
            ]
        );

        return redirect()->route('jadwal.index')->with('success', 'Schedule updated');
    }
}
