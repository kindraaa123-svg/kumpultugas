<?php

namespace App\Http\Controllers;

use App\Helpers\ActivityLogger;
use App\Models\AcademicYear;
use App\Models\Assignment;
use App\Models\Block;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssignmentController extends Controller
{
    private function canManageAssignments(): bool
    {
        if (session('level') != 2) {
            return false;
        }

        $role = session('role');
        if (is_string($role) && mb_strtolower($role) === 'guru') {
            return true;
        }

        $teacher = DB::table('teacher')->where('userid', session('userid'))->first();

        return $teacher && (int) $teacher->roleid === 3;
    }

    public function index()
    {
        $system = DB::table('system')->first();
        $selectedScheduleId = request()->query('scheduleid');
        $selectedBlockId = request()->query('block_id');
        $selectedAcademicYearId = request()->query('academic_year_id');
        $selectedRoom = null;
        $assignments = collect();
        $submittedIds = [];

        $activeYear = AcademicYear::where('is_active', 1)->first();
        $selectedYear = null;
        if ($selectedAcademicYearId && $selectedAcademicYearId !== 'all') {
            $selectedYear = AcademicYear::where('academic_year_id', $selectedAcademicYearId)->first();
        }
        if (! $selectedYear) {
            $selectedYear = $activeYear;
        }

        if ($selectedYear) {
            $selectedAcademicYearId = $selectedYear->academic_year_id;
        }

        $blocks = collect();
        if ($selectedAcademicYearId && $selectedAcademicYearId !== 'all') {
            $blocks = Block::where('academic_year_id', $selectedAcademicYearId)
                ->orderBy('date_start', 'asc')
                ->get();
        }

        if (! $selectedBlockId) {
            $selectedBlockId = $blocks->first()->block_id ?? 'all';
        }

        if (session('level') == 3) {
            $student = DB::table('student')->where('userid', session('userid'))->first();
            if ($student && $selectedScheduleId) {
                $selectedRoom = DB::table('ruang_mapel')
                    ->join('schedule', 'schedule.scheduleid', '=', 'ruang_mapel.scheduleid')
                    ->join('course', 'course.courseid', '=', 'schedule.courseid')
                    ->join('class', 'class.classid', '=', 'schedule.classid')
                    ->leftJoin('block', 'block.block_id', '=', 'schedule.block_id')
                    ->leftJoin('academic_year', 'academic_year.academic_year_id', '=', 'schedule.academic_year_id')
                    ->leftJoin('teacher', 'teacher.teacherid', '=', 'schedule.teacherid')
                    ->where('schedule.scheduleid', $selectedScheduleId)
                    ->where('schedule.classid', $student->classid)
                    ->select(
                        'schedule.scheduleid',
                        'schedule.academic_year_id',
                        'schedule.block_id',
                        'schedule.session',
                        'course.coursename',
                        'class.classname',
                        'block.name as block_name',
                        'academic_year.name as academic_year_name',
                        'teacher.name as teacher_name'
                    )
                    ->first();

                if ($selectedRoom) {
                    $assignments = Assignment::with('schedule.course', 'schedule.classroom')
                        ->where('scheduleid', $selectedRoom->scheduleid)
                        ->orderBy('time_end', 'desc')
                        ->get();

                    $submittedIds = DB::table('quest')
                        ->where('studentid', $student->studentid)
                        ->pluck('assignmentid')
                        ->toArray();
                }
            }
        } elseif (session('level') == 2) {
            $teacher = DB::table('teacher')->where('userid', session('userid'))->first();
            if ($teacher && $selectedScheduleId) {
                $selectedRoom = DB::table('ruang_mapel')
                    ->join('schedule', 'schedule.scheduleid', '=', 'ruang_mapel.scheduleid')
                    ->join('course', 'course.courseid', '=', 'schedule.courseid')
                    ->join('class', 'class.classid', '=', 'schedule.classid')
                    ->leftJoin('block', 'block.block_id', '=', 'schedule.block_id')
                    ->leftJoin('academic_year', 'academic_year.academic_year_id', '=', 'schedule.academic_year_id')
                    ->leftJoin('teacher', 'teacher.teacherid', '=', 'schedule.teacherid')
                    ->where('schedule.scheduleid', $selectedScheduleId)
                    ->where('schedule.teacherid', $teacher->teacherid)
                    ->select(
                        'schedule.scheduleid',
                        'schedule.academic_year_id',
                        'schedule.block_id',
                        'schedule.session',
                        'course.coursename',
                        'class.classname',
                        'block.name as block_name',
                        'academic_year.name as academic_year_name',
                        'teacher.name as teacher_name'
                    )
                    ->first();

                if ($selectedRoom) {
                    $assignments = Assignment::with('schedule.course', 'schedule.classroom')
                        ->where('scheduleid', $selectedRoom->scheduleid)
                        ->orderBy('time_end', 'desc')
                        ->get();
                }
            }
        }

        $rooms = collect();
        if (! $selectedScheduleId) {
            $roomsQuery = DB::table('ruang_mapel')
                ->join('schedule', 'schedule.scheduleid', '=', 'ruang_mapel.scheduleid')
                ->join('course', 'course.courseid', '=', 'schedule.courseid')
                ->join('class', 'class.classid', '=', 'schedule.classid')
                ->leftJoin('block', 'block.block_id', '=', 'schedule.block_id')
                ->leftJoin('academic_year', 'academic_year.academic_year_id', '=', 'schedule.academic_year_id')
                ->leftJoin('teacher', 'teacher.teacherid', '=', 'schedule.teacherid')
                ->leftJoin('assignment', 'assignment.scheduleid', '=', 'schedule.scheduleid')
                ->select(
                    'schedule.scheduleid',
                    'schedule.academic_year_id',
                    'academic_year.name as academic_year_name',
                    'schedule.block_id',
                    'block.name as block_name',
                    'schedule.session',
                    'course.coursename',
                    'class.classname',
                    'teacher.name as teacher_name',
                    DB::raw('COUNT(DISTINCT assignment.assignmentid) as total_tasks')
                )
                ->groupBy(
                    'schedule.scheduleid',
                    'schedule.academic_year_id',
                    'academic_year.name',
                    'schedule.block_id',
                    'block.name',
                    'schedule.session',
                    'course.coursename',
                    'class.classname',
                    'teacher.name'
                )
                ->orderBy('course.coursename');

            if ($selectedAcademicYearId && $selectedAcademicYearId !== 'all') {
                $roomsQuery->where('schedule.academic_year_id', $selectedAcademicYearId);
            }
            if ($selectedBlockId && $selectedBlockId !== 'all') {
                $roomsQuery->where('schedule.block_id', $selectedBlockId);
            }

            if (session('level') == 3) {
                $student = DB::table('student')->where('userid', session('userid'))->first();
                if ($student) {
                    $roomsQuery->where('schedule.classid', $student->classid);
                } else {
                    $roomsQuery->whereRaw('1=0');
                }
            } elseif (session('level') == 2) {
                $teacher = DB::table('teacher')->where('userid', session('userid'))->first();
                if ($teacher) {
                    $roomsQuery->where('schedule.teacherid', $teacher->teacherid);
                } else {
                    $roomsQuery->whereRaw('1=0');
                }
            } else {
                $roomsQuery->whereRaw('1=0');
            }

            $rooms = $roomsQuery->get();
        }

        $academicYears = AcademicYear::orderBy('name', 'desc')->get();

        echo view('all.header', compact('system'));
        echo view('all.menu', compact('system'));
        echo view('cyber.assignment.index', compact('assignments', 'submittedIds', 'rooms', 'selectedScheduleId', 'selectedRoom', 'blocks', 'selectedBlockId', 'academicYears', 'selectedAcademicYearId'));
        echo view('all.footer');
    }

    public function filterCourses(Request $request)
    {
        $blockId = $request->query('block_id');
        $academicYearId = $request->query('academic_year_id');

        $roomsQuery = DB::table('ruang_mapel')
            ->join('schedule', 'schedule.scheduleid', '=', 'ruang_mapel.scheduleid')
            ->join('course', 'course.courseid', '=', 'schedule.courseid')
            ->join('class', 'class.classid', '=', 'schedule.classid')
            ->leftJoin('block', 'block.block_id', '=', 'schedule.block_id')
            ->leftJoin('teacher', 'teacher.teacherid', '=', 'schedule.teacherid')
            ->leftJoin('academic_year', 'academic_year.academic_year_id', '=', 'schedule.academic_year_id')
            ->leftJoin('assignment', 'assignment.scheduleid', '=', 'schedule.scheduleid')
            ->select(
                'schedule.scheduleid',
                'schedule.academic_year_id',
                'academic_year.name as academic_year_name',
                'schedule.block_id',
                'block.name as block_name',
                'schedule.session',
                'course.coursename',
                'class.classname',
                'teacher.name as teacher_name',
                DB::raw('COUNT(DISTINCT assignment.assignmentid) as total_tasks')
            )
            ->groupBy(
                'schedule.scheduleid',
                'schedule.academic_year_id',
                'academic_year.name',
                'schedule.block_id',
                'block.name',
                'schedule.session',
                'course.coursename',
                'class.classname',
                'teacher.name'
            )
            ->orderBy('course.coursename');

        if (session('level') == 3) {
            $student = DB::table('student')->where('userid', session('userid'))->first();
            if ($student) {
                $roomsQuery->where('schedule.classid', $student->classid);
            } else {
                return response()->json(['html' => '']);
            }
        } elseif (session('level') == 2) {
            $teacher = DB::table('teacher')->where('userid', session('userid'))->first();
            if ($teacher) {
                $roomsQuery->where('schedule.teacherid', $teacher->teacherid);
            } else {
                return response()->json(['html' => '']);
            }
        } else {
            return response()->json(['html' => '']);
        }

        if ($academicYearId && $academicYearId !== 'all') {
            $roomsQuery->where('schedule.academic_year_id', $academicYearId);
        }

        if ($blockId && $blockId !== 'all') {
            $roomsQuery->where('schedule.block_id', $blockId);
        }

        $rooms = $roomsQuery->get();

        $html = '';
        foreach ($rooms as $room) {
            $html .= view('cyber.assignment.room_card', ['room' => $room])->render();
        }

        $blocks = collect();
        if ($academicYearId && $academicYearId !== 'all') {
            $blocks = Block::where('academic_year_id', $academicYearId)
                ->orderBy('date_start', 'asc')
                ->get();
        }

        $blocksOptionsHtml = '<option value="all">Semua Blok</option>';
        foreach ($blocks as $b) {
            $blocksOptionsHtml .= '<option value="'.$b->block_id.'">'.e($b->name).'</option>';
        }

        return response()->json(['html' => $html, 'blocksOptionsHtml' => $blocksOptionsHtml]);
    }

    public function storeRoom(Request $request)
    {
        if (! $this->canManageAssignments()) {
            return back()->with('error', 'Hanya guru yang bisa menambah ruang mapel');
        }

        $request->validate([
            'scheduleid' => 'required',
        ]);

        $teacher = DB::table('teacher')->where('userid', session('userid'))->first();
        if (! $teacher) {
            return back()->with('error', 'Data guru tidak ditemukan');
        }

        $schedule = DB::table('schedule')->where('scheduleid', $request->scheduleid)->first();
        if (! $schedule || (int) $schedule->teacherid !== (int) $teacher->teacherid) {
            return back()->with('error', 'Anda tidak berhak membuat ruang mapel untuk jadwal ini');
        }

        $exists = DB::table('ruang_mapel')->where('scheduleid', $schedule->scheduleid)->exists();
        if ($exists) {
            return back()->with('error', 'Ruang mapel untuk jadwal ini sudah ada');
        }

        DB::table('ruang_mapel')->insert([
            'scheduleid' => $schedule->scheduleid,
            'teacherid' => $teacher->teacherid,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($request->wantsJson()) {
            $room = DB::table('ruang_mapel')
                ->join('schedule', 'schedule.scheduleid', '=', 'ruang_mapel.scheduleid')
                ->join('course', 'course.courseid', '=', 'schedule.courseid')
                ->join('class', 'class.classid', '=', 'schedule.classid')
                ->leftJoin('block', 'block.block_id', '=', 'schedule.block_id')
                ->leftJoin('academic_year', 'academic_year.academic_year_id', '=', 'schedule.academic_year_id')
                ->leftJoin('teacher', 'teacher.teacherid', '=', 'schedule.teacherid')
                ->leftJoin('assignment', 'assignment.scheduleid', '=', 'schedule.scheduleid')
                ->where('schedule.scheduleid', $schedule->scheduleid)
                ->select(
                    'schedule.scheduleid',
                    'schedule.academic_year_id',
                    'academic_year.name as academic_year_name',
                    'schedule.block_id',
                    'block.name as block_name',
                    'schedule.session',
                    'course.coursename',
                    'class.classname',
                    'teacher.name as teacher_name',
                    DB::raw('COUNT(DISTINCT assignment.assignmentid) as total_tasks')
                )
                ->groupBy(
                    'schedule.scheduleid',
                    'schedule.academic_year_id',
                    'academic_year.name',
                    'schedule.block_id',
                    'block.name',
                    'schedule.session',
                    'course.coursename',
                    'class.classname',
                    'teacher.name'
                )
                ->first();

            $roomHtml = $room ? view('cyber.assignment.room_card', ['room' => $room])->render() : '';

            return response()->json([
                'message' => 'Ruang mapel berhasil dibuat',
                'scheduleid' => $schedule->scheduleid,
                'roomHtml' => $roomHtml,
            ]);
        }

        return back()->with('success', 'Ruang mapel berhasil dibuat');
    }

    public function deleteRoom(Request $request)
    {
        if (! $this->canManageAssignments()) {
            return back()->with('error', 'Hanya guru yang bisa menghapus ruang mapel');
        }

        $request->validate([
            'scheduleid' => 'required',
        ]);

        $teacher = DB::table('teacher')->where('userid', session('userid'))->first();
        if (! $teacher) {
            return back()->with('error', 'Data guru tidak ditemukan');
        }

        $schedule = DB::table('schedule')->where('scheduleid', $request->scheduleid)->first();
        if (! $schedule || (int) $schedule->teacherid !== (int) $teacher->teacherid) {
            return back()->with('error', 'Anda tidak berhak menghapus ruang mapel ini');
        }

        DB::transaction(function () use ($schedule) {
            $assignmentIds = DB::table('assignment')
                ->where('scheduleid', $schedule->scheduleid)
                ->pluck('assignmentid')
                ->all();

            if (! empty($assignmentIds)) {
                DB::table('quest')->whereIn('assignmentid', $assignmentIds)->delete();
                DB::table('assignment')->whereIn('assignmentid', $assignmentIds)->delete();
            }

            DB::table('ruang_mapel')->where('scheduleid', $schedule->scheduleid)->delete();
        });

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Ruang mapel berhasil dihapus',
                'scheduleid' => $schedule->scheduleid,
            ]);
        }

        return back()->with('success', 'Ruang mapel berhasil dihapus');
    }

    public function filterTeacherSchedules(Request $request)
    {
        if (! $this->canManageAssignments()) {
            return response()->json(['scheduleOptionsHtml' => '<option value="">-- Pilih Jadwal --</option>', 'blocksOptionsHtml' => '<option value="">-- Pilih Blok --</option>']);
        }

        $teacher = DB::table('teacher')->where('userid', session('userid'))->first();
        if (! $teacher) {
            return response()->json(['scheduleOptionsHtml' => '<option value="">-- Pilih Jadwal --</option>', 'blocksOptionsHtml' => '<option value="">-- Pilih Blok --</option>']);
        }

        $academicYearId = $request->query('academic_year_id');
        $blockId = $request->query('block_id');

        $blocksOptionsHtml = '<option value="">-- Pilih Blok --</option>';
        if ($academicYearId && $academicYearId !== 'all') {
            $blocks = Block::where('academic_year_id', $academicYearId)
                ->orderBy('date_start', 'asc')
                ->get();

            foreach ($blocks as $b) {
                $blocksOptionsHtml .= '<option value="'.$b->block_id.'">'.e($b->name).'</option>';
            }
        }

        $scheduleOptionsHtml = '<option value="">-- Pilih Jadwal --</option>';
        if ($academicYearId && $academicYearId !== 'all' && $blockId) {
            $schedules = DB::table('schedule')
                ->join('class', 'class.classid', '=', 'schedule.classid')
                ->join('course', 'course.courseid', '=', 'schedule.courseid')
                ->where('schedule.teacherid', $teacher->teacherid)
                ->where('schedule.academic_year_id', $academicYearId)
                ->where('schedule.block_id', $blockId)
                ->select('schedule.scheduleid', 'class.classname', 'course.coursename', 'schedule.session')
                ->orderBy('class.classname')
                ->orderBy('course.coursename')
                ->orderBy('schedule.session')
                ->get();

            foreach ($schedules as $s) {
                $label = e($s->classname.' - '.$s->coursename.' (Sesi '.$s->session.')');
                $scheduleOptionsHtml .= '<option value="'.$s->scheduleid.'">'.$label.'</option>';
            }
        }

        return response()->json(['scheduleOptionsHtml' => $scheduleOptionsHtml, 'blocksOptionsHtml' => $blocksOptionsHtml]);
    }

    public function deleteAjax(Request $request)
    {
        if (! $this->canManageAssignments()) {
            return response()->json(['message' => 'Hanya guru yang bisa menghapus tugas'], 403);
        }

        $request->validate([
            'assignmentid' => 'required',
        ]);

        $teacher = DB::table('teacher')->where('userid', session('userid'))->first();
        if (! $teacher) {
            return response()->json(['message' => 'Data guru tidak ditemukan'], 404);
        }

        $assignment = DB::table('assignment')->where('assignmentid', $request->assignmentid)->first();
        if (! $assignment) {
            return response()->json(['message' => 'Tugas tidak ditemukan'], 404);
        }

        $schedule = DB::table('schedule')->where('scheduleid', $assignment->scheduleid)->first();
        if (! $schedule || (int) $schedule->teacherid !== (int) $teacher->teacherid) {
            return response()->json(['message' => 'Anda tidak berhak menghapus tugas ini'], 403);
        }

        DB::transaction(function () use ($assignment) {
            DB::table('quest')->where('assignmentid', $assignment->assignmentid)->delete();
            DB::table('assignment')->where('assignmentid', $assignment->assignmentid)->delete();
        });

        return response()->json([
            'message' => 'Tugas berhasil dihapus',
            'assignmentid' => $assignment->assignmentid,
        ]);
    }

    public function filter(Request $request)
    {
        $status = $request->query('status'); // 'all', 'completed', 'pending'
        $scheduleId = $request->query('scheduleid');

        $query = Assignment::with('schedule.course', 'schedule.classroom');
        $student = null;
        $submittedIds = [];

        if (session('level') == 3) {
            $student = DB::table('student')->where('userid', session('userid'))->first();
            if ($student) {
                $query->whereHas('schedule', function ($q) use ($student) {
                    $q->where('classid', $student->classid);
                });

                // Ambil ID tugas yang sudah dikumpulkan
                $submittedIds = DB::table('quest')
                    ->where('studentid', $student->studentid)
                    ->pluck('assignmentid')
                    ->toArray();

            } else {
                return response()->json([]);
            }
        } elseif (session('level') == 2) {
            $teacher = DB::table('teacher')->where('userid', session('userid'))->first();
            if ($teacher) {
                $query->whereHas('schedule', function ($q) use ($teacher) {
                    $q->where('teacherid', $teacher->teacherid);
                });
            } else {
                return response()->json([]);
            }
        }

        if ($scheduleId) {
            $query->where('scheduleid', $scheduleId);
        }

        $assignments = $query->get();

        // Filter status manually since it involves a relationship check (quest table)
        if ($status && $status != 'all' && session('level') == 3 && $student) {
            $now = now();
            $assignments = $assignments->filter(function ($assignment) use ($status, $student, $now) {
                $isSubmitted = DB::table('quest')
                    ->where('assignmentid', $assignment->assignmentid)
                    ->where('studentid', $student->studentid)
                    ->exists();

                if ($status == 'completed') {
                    return $isSubmitted;
                } elseif ($status == 'pending') {
                    return ! $isSubmitted && $now->lte($assignment->time_end);
                } elseif ($status == 'not_done') {
                    return ! $isSubmitted && $now->gt($assignment->time_end);
                }

                return true;
            });
        }

        // Return partial view or JSON data
        // For simplicity, let's return the HTML of the cards directly
        // We need to loop through assignments and generate HTML string

        $html = '';
        foreach ($assignments as $assignment) {
            $html .= view('cyber.assignment.card', compact('assignment', 'submittedIds'))->render();
        }

        return response()->json(['html' => $html]);
    }

    public function review(Request $request)
    {
        $system = DB::table('system')->first();
        if (session('level') != 2) {
            return redirect()->route('assignment.index')->with('error', 'Hanya guru yang bisa mengakses halaman ini');
        }

        $teacher = DB::table('teacher')->where('userid', session('userid'))->first();
        if (! $teacher) {
            return redirect()->route('assignment.index')->with('error', 'Data guru tidak ditemukan');
        }

        $classid = $request->query('classid');
        $courseid = $request->query('courseid');
        $blockId = $request->query('block_id');
        $academicYearId = $request->query('academic_year_id');
        $assignmentid = $request->query('assignmentid');

        $scheduleGroups = DB::table('schedule')
            ->leftJoin('class', 'class.classid', '=', 'schedule.classid')
            ->leftJoin('course', 'course.courseid', '=', 'schedule.courseid')
            ->leftJoin('block', 'block.block_id', '=', 'schedule.block_id')
            ->leftJoin('academic_year', 'academic_year.academic_year_id', '=', 'schedule.academic_year_id')
            ->leftJoin('assignment', 'assignment.scheduleid', '=', 'schedule.scheduleid')
            ->where('schedule.teacherid', $teacher->teacherid)
            ->select(
                'schedule.classid',
                'class.classname',
                'schedule.courseid',
                'course.coursename',
                'schedule.block_id',
                'block.name as block_name',
                'schedule.academic_year_id',
                'academic_year.name as academic_year_name',
                'academic_year.start_date as academic_year_start',
                'academic_year.end_date as academic_year_end',
                DB::raw('COUNT(DISTINCT assignment.assignmentid) as total_assignments')
            )
            ->groupBy(
                'schedule.classid',
                'class.classname',
                'schedule.courseid',
                'course.coursename',
                'schedule.block_id',
                'block.name',
                'schedule.academic_year_id',
                'academic_year.name',
                'academic_year.start_date',
                'academic_year.end_date'
            )
            ->orderBy('class.classname')
            ->orderBy('course.coursename')
            ->orderBy('schedule.block_id')
            ->get();

        $filterClasses = $scheduleGroups
            ->map(fn ($g) => (object) ['classid' => $g->classid, 'classname' => $g->classname])
            ->unique('classid')
            ->values();
        $filterBlocks = $scheduleGroups
            ->map(fn ($g) => (object) ['block_id' => $g->block_id, 'block_name' => $g->block_name])
            ->unique('block_id')
            ->values();
        $filterAcademicYears = $scheduleGroups
            ->map(fn ($g) => (object) ['academic_year_id' => $g->academic_year_id, 'academic_year_name' => $g->academic_year_name])
            ->unique('academic_year_id')
            ->values();

        $selectedGroup = null;
        if ($classid && $courseid && $blockId && $academicYearId) {
            $selectedGroup = $scheduleGroups->first(function ($g) use ($classid, $courseid, $blockId, $academicYearId) {
                return (string) $g->classid === (string) $classid
                    && (string) $g->courseid === (string) $courseid
                    && (string) $g->block_id === (string) $blockId
                    && (string) $g->academic_year_id === (string) $academicYearId;
            });
        }

        $assignments = collect();
        if ($selectedGroup) {
            $assignments = DB::table('assignment')
                ->leftJoin('schedule', 'schedule.scheduleid', '=', 'assignment.scheduleid')
                ->leftJoin('course', 'course.courseid', '=', 'schedule.courseid')
                ->leftJoin('block', 'block.block_id', '=', 'schedule.block_id')
                ->leftJoin('academic_year', 'academic_year.academic_year_id', '=', 'schedule.academic_year_id')
                ->where('schedule.teacherid', $teacher->teacherid)
                ->where('schedule.classid', $classid)
                ->where('schedule.courseid', $courseid)
                ->where('schedule.block_id', $blockId)
                ->where('schedule.academic_year_id', $academicYearId)
                ->select(
                    'assignment.*',
                    'course.coursename',
                    'block.name as block_name',
                    'academic_year.name as academic_year_name',
                    'schedule.classid',
                    'schedule.courseid',
                    'schedule.block_id',
                    'schedule.academic_year_id'
                )
                ->orderBy('assignment.time_end', 'desc')
                ->get();
        }

        $students = collect();
        $submissions = collect();
        $submittedIds = [];
        if ($selectedGroup && $assignmentid) {
            $students = DB::table('student')->where('classid', $classid)->get();
            $submissions = DB::table('quest')
                ->where('assignmentid', $assignmentid)
                ->get()
                ->keyBy('studentid');
            $submittedIds = $submissions->keys()->all();
        }

        $selectedAssignment = null;
        if ($selectedGroup && $assignmentid) {
            $selectedAssignment = $assignments->first(function ($a) use ($assignmentid) {
                return (string) $a->assignmentid === (string) $assignmentid;
            });
        }

        echo view('all.header', compact('system'));
        echo view('all.menu', compact('system'));
        echo view('cyber.assignment.review', compact('scheduleGroups', 'filterClasses', 'filterBlocks', 'filterAcademicYears', 'selectedGroup', 'assignments', 'selectedAssignment', 'students', 'submissions', 'classid', 'courseid', 'blockId', 'academicYearId', 'assignmentid', 'submittedIds'));
        echo view('all.footer');
    }

    public function reviewFilter(Request $request)
    {
        if (session('level') != 2) {
            return response()->json(['html' => '']);
        }

        $teacher = DB::table('teacher')->where('userid', session('userid'))->first();
        if (! $teacher) {
            return response()->json(['html' => '']);
        }

        $classid = $request->query('classid');
        $blockId = $request->query('block_id');
        $academicYearId = $request->query('academic_year_id');

        $scheduleGroups = DB::table('schedule')
            ->leftJoin('class', 'class.classid', '=', 'schedule.classid')
            ->leftJoin('course', 'course.courseid', '=', 'schedule.courseid')
            ->leftJoin('block', 'block.block_id', '=', 'schedule.block_id')
            ->leftJoin('academic_year', 'academic_year.academic_year_id', '=', 'schedule.academic_year_id')
            ->leftJoin('assignment', 'assignment.scheduleid', '=', 'schedule.scheduleid')
            ->where('schedule.teacherid', $teacher->teacherid)
            ->when($classid && $classid !== 'all', fn ($q) => $q->where('schedule.classid', $classid))
            ->when($blockId && $blockId !== 'all', fn ($q) => $q->where('schedule.block_id', $blockId))
            ->when($academicYearId && $academicYearId !== 'all', fn ($q) => $q->where('schedule.academic_year_id', $academicYearId))
            ->select(
                'schedule.classid',
                'class.classname',
                'schedule.courseid',
                'course.coursename',
                'schedule.block_id',
                'block.name as block_name',
                'schedule.academic_year_id',
                'academic_year.name as academic_year_name',
                'academic_year.start_date as academic_year_start',
                'academic_year.end_date as academic_year_end',
                DB::raw('COUNT(DISTINCT assignment.assignmentid) as total_assignments')
            )
            ->groupBy(
                'schedule.classid',
                'class.classname',
                'schedule.courseid',
                'course.coursename',
                'schedule.block_id',
                'block.name',
                'schedule.academic_year_id',
                'academic_year.name',
                'academic_year.start_date',
                'academic_year.end_date'
            )
            ->orderBy('class.classname')
            ->orderBy('course.coursename')
            ->orderBy('schedule.block_id')
            ->get();

        $html = view('cyber.assignment.review_group_rows', compact('scheduleGroups'))->render();

        return response()->json(['html' => $html]);
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
        if (! $teacher || ! $assignment) {
            return back()->with('error', 'Data tidak valid');
        }
        $schedule = DB::table('schedule')->where('scheduleid', $assignment->scheduleid)->first();
        if (! $schedule || $schedule->teacherid != $teacher->teacherid) {
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

        ActivityLogger::log("Memberi Nilai: {$request->score} untuk siswa ID {$request->studentid} pada tugas ID {$request->assignmentid}", $request->ip());

        return back()->with('success', 'Nilai tersimpan');
    }

    public function show($id)
    {
        $system = DB::table('system')->first();
        $assignment = Assignment::with('schedule.course', 'schedule.classroom', 'schedule.teacher')->where('assignmentid', $id)->first();

        if (! $assignment) {
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
            'description' => 'nullable',
        ]);

        $assignment = Assignment::where('assignmentid', $request->assignmentid)->first();
        if (! $assignment) {
            return back()->with('error', 'Tugas tidak ditemukan');
        }

        // Check if deadline has passed
        if (now()->gt($assignment->time_end)) {
            return back()->with('error', 'Gagal: Waktu pengumpulan sudah habis (Deadline: '.date('d M Y, H:i', strtotime($assignment->time_end)).')');
        }

        $student = DB::table('student')->where('userid', session('userid'))->first();
        if (! $student) {
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
                    'studentid' => $student->studentid,
                ],
                [
                    'file' => json_encode($stored),
                    'description' => $request->description,
                    'created_at' => now(),
                    'updated_at' => now(),
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

                ActivityLogger::log("Mengumpulkan Tugas: {$assignment->name} (Auto Score: {$assignment->auto_score})", $request->ip());
            } else {
                ActivityLogger::log("Mengumpulkan Tugas: {$assignment->name}", $request->ip());
            }

            return back()->with('success', 'Tugas telah dikumpul');
        }

        return back()->with('error', 'Gagal mengupload file');
    }

    public function store(Request $request)
    {
        if (! $this->canManageAssignments()) {
            return back()->with('error', 'Hanya guru yang bisa menambah tugas');
        }

        $request->validate([
            'scheduleid' => 'required',
            'name' => 'required',
            'description' => 'nullable',
            'grading_mode' => 'required|in:auto,manual',
            'auto_score' => 'nullable|integer|min:0|max:100',
            'time_start' => 'required|date',
            'time_end' => 'required|date|after:time_start',
        ]);

        if (session('level') == 2) {
            $teacher = DB::table('teacher')->where('userid', session('userid'))->first();
            if ($teacher) {
                $schedule = DB::table('schedule')->where('scheduleid', $request->scheduleid)->first();
                if (! $schedule || $schedule->teacherid != $teacher->teacherid) {
                    return back()->with('error', 'Anda tidak berhak membuat tugas untuk jadwal ini');
                }
            } else {
                return back()->with('error', 'Data guru tidak ditemukan');
            }
        }

        $created = Assignment::create([
            'scheduleid' => $request->scheduleid,
            'name' => $request->name,
            'description' => $request->description,
            'grading_mode' => $request->grading_mode,
            'auto_score' => $request->grading_mode === 'auto' ? $request->auto_score : null,
            'time_start' => $request->time_start,
            'time_end' => $request->time_end,
            'created_at' => now(),
        ]);

        $assignment = Assignment::with('schedule.course', 'schedule.classroom')
            ->where('assignmentid', $created->assignmentid)
            ->first();

        if ($request->wantsJson()) {
            $cardHtml = $assignment ? view('cyber.assignment.card', ['assignment' => $assignment, 'submittedIds' => []])->render() : '';
            $modalHtml = $assignment ? view('cyber.assignment.edit_modal', ['assignment' => $assignment])->render() : '';

            return response()->json([
                'message' => 'Tugas berhasil ditambahkan',
                'assignmentid' => $created->assignmentid,
                'cardHtml' => $cardHtml,
                'modalHtml' => $modalHtml,
            ]);
        }

        $schedule = DB::table('schedule')->where('scheduleid', $request->scheduleid)->first();
        $params = ['scheduleid' => $request->scheduleid];
        if ($schedule) {
            $params['academic_year_id'] = $schedule->academic_year_id ?? null;
            $params['block_id'] = $schedule->block_id ?? null;
        }

        return redirect()->route('assignment.index', $params)->with('success', 'Tugas berhasil ditambahkan');
    }

    public function update(Request $request)
    {
        if (! $this->canManageAssignments()) {
            return back()->with('error', 'Hanya guru yang bisa mengubah tugas');
        }

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
        if (! $assignment) {
            return back()->with('error', 'Tugas tidak ditemukan');
        }

        if (session('level') == 2) {
            $teacher = DB::table('teacher')->where('userid', session('userid'))->first();
            $schedule = DB::table('schedule')->where('scheduleid', $assignment->scheduleid)->first();
            if (! $teacher || ! $schedule || $schedule->teacherid != $teacher->teacherid) {
                return back()->with('error', 'Anda tidak berhak mengedit tugas ini');
            }
        }

        $assignment->update([
            'name' => $request->name,
            'description' => $request->description,
            'grading_mode' => $request->grading_mode,
            'auto_score' => $request->grading_mode === 'auto' ? $request->auto_score : null,
            'time_start' => $request->time_start,
            'time_end' => $request->time_end,
        ]);

        $updated = Assignment::with('schedule.course', 'schedule.classroom')
            ->where('assignmentid', $assignment->assignmentid)
            ->first();

        if ($request->wantsJson()) {
            $cardHtml = $updated ? view('cyber.assignment.card', ['assignment' => $updated, 'submittedIds' => []])->render() : '';
            $modalHtml = $updated ? view('cyber.assignment.edit_modal', ['assignment' => $updated])->render() : '';

            return response()->json([
                'message' => 'Tugas berhasil diperbarui',
                'assignmentid' => $assignment->assignmentid,
                'cardHtml' => $cardHtml,
                'modalHtml' => $modalHtml,
            ]);
        }

        $schedule = DB::table('schedule')->where('scheduleid', $assignment->scheduleid)->first();
        $params = ['scheduleid' => $assignment->scheduleid];
        if ($schedule) {
            $params['academic_year_id'] = $schedule->academic_year_id ?? null;
            $params['block_id'] = $schedule->block_id ?? null;
        }

        return redirect()->route('assignment.index', $params)->with('success', 'Tugas berhasil diperbarui');
    }

    public function delete($id)
    {
        if (! $this->canManageAssignments()) {
            return back()->with('error', 'Hanya guru yang bisa menghapus tugas');
        }

        $assignment = Assignment::where('assignmentid', $id)->first();
        if (! $assignment) {
            return back()->with('error', 'Tugas tidak ditemukan');
        }

        if (session('level') == 2) {
            $teacher = DB::table('teacher')->where('userid', session('userid'))->first();
            $schedule = DB::table('schedule')->where('scheduleid', $assignment->scheduleid)->first();
            if (! $teacher || ! $schedule || $schedule->teacherid != $teacher->teacherid) {
                return back()->with('error', 'Anda tidak berhak menghapus tugas ini');
            }
        }

        DB::table('quest')->where('assignmentid', $assignment->assignmentid)->delete();
        $assignment->delete();

        return back()->with('success', 'Assignment deleted');
    }
}
