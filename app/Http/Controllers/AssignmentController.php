<?php

namespace App\Http\Controllers;


use App\Models\Assignment;
use App\Models\Schedule;
use App\Models\Classroom;
use App\Models\AcademicYear;
use App\Models\Block;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssignmentController extends Controller
{
    public function index()
    {
        $system = DB::table('system')->first();
        $query = Assignment::with('schedule.course', 'schedule.classroom');
        if (session('level') == 3) {
            $student = DB::table('student')->where('userid', session('userid'))->first();
            if ($student) {
                $query->whereHas('schedule', function($q) use ($student) {
                    $q->where('classid', $student->classid);
                });
            } else {
                $query->whereRaw('1=0');
            }
        }
        $assignments = $query->get();

        $activeYear = AcademicYear::where('is_active', 1)->first();
        $today = date('Y-m-d');
        $activeBlock = Block::where('academic_year_id', $activeYear->academic_year_id)
            ->where('date_start', '<=', $today)
            ->where('date_end', '>=', $today)
            ->first();
        
        if (!$activeBlock) {
            $activeBlock = Block::where('academic_year_id', $activeYear->academic_year_id)->first();
        }

        $schedules = Schedule::with(['course', 'classroom'])
            ->where('academic_year_id', $activeYear->academic_year_id)
            ->where('block_id', $activeBlock->block_id)
            ->get();

        echo view('all.header', compact('system'));
        echo view('all.menu', compact('system'));
        echo view('cyber.assignment.index', compact('assignments', 'schedules'));
        echo view('all.footer');
    }

    public function review(Request $request)
    {
        $system = DB::table('system')->first();
        if (session('level') != 2) {
            return redirect()->route('assignment.index')->with('error', 'Hanya guru yang bisa mengakses halaman ini');
        }

        $teacher = DB::table('teacher')->where('userid', session('userid'))->first();
        if (!$teacher) {
            return redirect()->route('assignment.index')->with('error', 'Data guru tidak ditemukan');
        }

        $classes = DB::table('schedule')
            ->leftJoin('class', 'class.classid', '=', 'schedule.classid')
            ->where('teacherid', $teacher->teacherid)
            ->select('schedule.classid', 'class.classname')
            ->distinct()
            ->get();

        $classid = $request->query('classid');
        $assignmentid = $request->query('assignmentid');

        $assignments = collect();
        if ($classid) {
            $assignments = DB::table('assignment')
                ->leftJoin('schedule', 'schedule.scheduleid', '=', 'assignment.scheduleid')
                ->leftJoin('course', 'course.courseid', '=', 'schedule.courseid')
                ->where('schedule.classid', $classid)
                ->where('schedule.teacherid', $teacher->teacherid)
                ->select('assignment.*', 'course.coursename')
                ->orderBy('assignment.time_end', 'desc')
                ->get();
        }

        $students = collect();
        $submissions = collect();
        $submittedIds = [];
        if ($classid && $assignmentid) {
            $students = DB::table('student')->where('classid', $classid)->get();
            $submissions = DB::table('quest')
                ->where('assignmentid', $assignmentid)
                ->get()
                ->keyBy('studentid');
            $submittedIds = $submissions->keys()->all();
        }

        echo view('all.header', compact('system'));
        echo view('all.menu', compact('system'));
        echo view('cyber.assignment.review', compact('classes', 'assignments', 'students', 'submissions', 'classid', 'assignmentid', 'submittedIds'));
        echo view('all.footer');
    }

    public function grade(Request $request)
    {
        if (session('level') != 2) {
            return back()->with('error', 'Hanya guru yang bisa memberi nilai');
        }
        $request->validate([
            'assignmentid' => 'required',
            'studentid' => 'required',
            'score' => 'nullable|integer|min:0|max:100',
            'feedback' => 'nullable|string',
        ]);

        $teacher = DB::table('teacher')->where('userid', session('userid'))->first();
        $assignment = Assignment::where('assignmentid', $request->assignmentid)->first();
        if (!$teacher || !$assignment) {
            return back()->with('error', 'Data tidak valid');
        }
        $schedule = DB::table('schedule')->where('scheduleid', $assignment->scheduleid)->first();
        if (!$schedule || $schedule->teacherid != $teacher->teacherid) {
            return back()->with('error', 'Anda tidak berhak menilai tugas ini');
        }

        DB::table('quest')
            ->where('assignmentid', $request->assignmentid)
            ->where('studentid', $request->studentid)
            ->update([
                'score' => $request->score,
                'feedback' => $request->feedback,
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Nilai tersimpan');
    }
    public function show($id)
    {
        $system = DB::table('system')->first();
        $assignment = Assignment::with('schedule.course', 'schedule.classroom', 'schedule.teacher')->where('assignmentid', $id)->first();
        
        if (!$assignment) {
            return redirect()->route('assignment.index')->with('error', 'Tugas tidak ditemukan');
        }

        // Ambil data pengumpulan jika ada (misal untuk siswa yang login)
        $quest = null;
        $isLate = now()->gt($assignment->time_end);

        if (session('level') == 3) {
            $student = DB::table('student')->where('userid', session('userid'))->first();
            if ($student) {
                $quest = DB::table('quest')->where('assignmentid', $id)->where('studentid', $student->studentid)->first();
            }
        }

        echo view('all.header', compact('system'));
        echo view('all.menu', compact('system'));
        echo view('cyber.assignment.show', compact('assignment', 'quest', 'isLate'));
        echo view('all.footer');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'assignmentid' => 'required',
            'files' => 'required|array',
            'files.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png,zip|max:10240',
            'description' => 'nullable'
        ]);

        $assignment = Assignment::where('assignmentid', $request->assignmentid)->first();
        if (!$assignment) {
            return back()->with('error', 'Tugas tidak ditemukan');
        }

        // Check if deadline has passed
        if (now()->gt($assignment->time_end)) {
            return back()->with('error', 'Gagal: Waktu pengumpulan sudah habis (Deadline: ' . date('d M Y, H:i', strtotime($assignment->time_end)) . ')');
        }

        $student = DB::table('student')->where('userid', session('userid'))->first();
        if (!$student) {
            return back()->with('error', 'Data siswa tidak ditemukan');
        }

        if ($request->hasFile('files')) {
            $stored = [];
            foreach ($request->file('files') as $file) {
                $stored[] = $file->store('quests', 'public');
            }

            DB::table('quest')->updateOrInsert(
                [
                    'assignmentid' => $request->assignmentid,
                    'studentid' => $student->studentid
                ],
                [
                    'file' => json_encode($stored),
                    'description' => $request->description,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );

            $assignment = Assignment::where('assignmentid', $request->assignmentid)->first();
            if ($assignment && $assignment->grading_mode === 'auto' && $assignment->auto_score !== null) {
                DB::table('quest')
                    ->where('assignmentid', $request->assignmentid)
                    ->where('studentid', $student->studentid)
                    ->update([
                        'score' => $assignment->auto_score,
                        'updated_at' => now(),
                    ]);
            }

            return back()->with('success', 'Tugas telah dikumpul');
        }

        return back()->with('error', 'Gagal mengupload file');
    }

    public function store(Request $request)
    {
        $request->validate([
            'scheduleid' => 'required',
            'name' => 'required',
            'description' => 'nullable',
            'grading_mode' => 'required|in:auto,manual',
            'auto_score' => 'nullable|integer|min:0|max:100',
            'time_start' => 'required|date',
            'time_end' => 'required|date|after:time_start',
        ]);

        Assignment::create([
            'scheduleid' => $request->scheduleid,
            'name' => $request->name,
            'description' => $request->description,
            'grading_mode' => $request->grading_mode,
            'auto_score' => $request->grading_mode === 'auto' ? $request->auto_score : null,
            'time_start' => $request->time_start,
            'time_end' => $request->time_end,
            'created_at' => now(),
        ]);

        return redirect()->route('assignment.index')->with('success', 'Tugas berhasil ditambahkan');
    }

    public function update(Request $request)
    {
        $request->validate([
            'assignmentid' => 'required',
            'name' => 'required',
            'description' => 'nullable',
            'grading_mode' => 'required|in:auto,manual',
            'auto_score' => 'nullable|integer|min:0|max:100',
            'time_start' => 'required|date',
            'time_end' => 'required|date|after:time_start',
        ]);

        $assignment = Assignment::where('assignmentid', $request->assignmentid)->first();
        if (!$assignment) {
            return back()->with('error', 'Tugas tidak ditemukan');
        }

        $assignment->update([
            'name' => $request->name,
            'description' => $request->description,
            'grading_mode' => $request->grading_mode,
            'auto_score' => $request->grading_mode === 'auto' ? $request->auto_score : null,
            'time_start' => $request->time_start,
            'time_end' => $request->time_end,
        ]);

        return back()->with('success', 'Tugas berhasil diperbarui');
    }

    public function delete($id)
    {
        Assignment::where('assignmentid', $id)->delete();
        return back()->with('success', 'Assignment deleted');
    }
}
