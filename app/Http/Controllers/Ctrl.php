<?php

namespace App\Http\Controllers;

use App\Helpers\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class Ctrl extends Controller
{
    private const LOGIN_MAX_ATTEMPTS = 5;

    private const LOGIN_DECAY_MINUTES = 30;

    private function loginFailKey(string $ip, string $username): string
    {
        $normalized = mb_strtolower(trim($username));

        return "login_fail:{$ip}:{$normalized}";
    }

    private function loginFailCount(string $key): int
    {
        return (int) Cache::get($key, 0);
    }

    private function incrementLoginFail(string $key): int
    {
        $count = $this->loginFailCount($key) + 1;
        Cache::put($key, $count, now()->addMinutes(self::LOGIN_DECAY_MINUTES));

        return $count;
    }

    private function clearLoginFail(string $key): void
    {
        Cache::forget($key);
    }

    public function notfound()
    {
        return response()->view('all.error', [], 404);
    }

    public function hakaksesPage()
    {
        $system = DB::table('system')->first();
        if (session('level') != 1 || session('role') != 'Superadmin') {
            return redirect('/home')->with('error', 'Access denied');
        }

        $groups = [
            'superadmin' => ['label' => 'Superadmin', 'level' => 'Employer', 'role' => 'Superadmin'],
            'admin' => ['label' => 'Admin', 'level' => 'Employer', 'role' => 'Admin'],
            'curiculum' => ['label' => 'Kurikulum', 'level' => 'Teacher', 'role' => 'Kurikulum'],
            'guru' => ['label' => 'Guru', 'level' => 'Teacher', 'role' => 'Guru'],
            'siswa' => ['label' => 'Siswa', 'level' => 'Student', 'role' => 'Siswa'],
        ];

        $menus = [
            'tugas' => 'Tugas',
            'jadwal' => 'Jadwal',
            'userdata' => 'Userdata',
            'data' => 'Data',
            'data_course' => 'Data - Mata Pelajaran',
            'data_class' => 'Data - Kelas',
            'data_academicyear' => 'Data - Tahun Ajaran',
            'data_block' => 'Data - Blok',
            'data_nilai' => 'Data Nilai',
            'trash' => 'Tong Sampah',
            'activity_log' => 'Activity Log',
            'backup_database' => 'Back up Database',
            'hak_akses' => 'Hak Akses',
            'pengaturan' => 'Pengaturan',
        ];

        $rows = DB::table('hakakses')
            ->whereIn('group_key', array_keys($groups))
            ->whereIn('menu_key', array_keys($menus))
            ->get();

        $matrix = [];
        foreach ($groups as $groupKey => $_g) {
            foreach ($menus as $menuKey => $_m) {
                $matrix[$groupKey][$menuKey] = false;
            }
        }

        foreach ($rows as $row) {
            $matrix[$row->group_key][$row->menu_key] = (bool) $row->allowed;
        }

        echo view('all.header', compact('system'));
        echo view('all.menu', compact('system'));
        echo view('superadmin.hakakses', compact('groups', 'menus', 'matrix'));
        echo view('all.footer');
    }

    public function hakaksesUpdate(Request $request)
    {
        if (session('level') != 1 || session('role') != 'Superadmin') {
            return redirect('/home')->with('error', 'Access denied');
        }

        $groups = ['superadmin', 'admin', 'curiculum', 'guru', 'siswa'];
        $menus = [
            'tugas',
            'jadwal',
            'userdata',
            'data',
            'data_course',
            'data_class',
            'data_academicyear',
            'data_block',
            'data_nilai',
            'trash',
            'activity_log',
            'backup_database',
            'hak_akses',
            'pengaturan',
        ];

        $input = $request->input('perm', []);
        $now = now();

        $rows = [];
        foreach ($groups as $groupKey) {
            foreach ($menus as $menuKey) {
                $allowed = isset($input[$groupKey]) && isset($input[$groupKey][$menuKey]);
                $rows[] = [
                    'group_key' => $groupKey,
                    'menu_key' => $menuKey,
                    'allowed' => $allowed,
                    'updated_at' => $now,
                    'created_at' => $now,
                ];
            }
        }

        DB::beginTransaction();
        try {
            DB::table('hakakses')
                ->whereIn('group_key', $groups)
                ->whereIn('menu_key', $menus)
                ->delete();

            DB::table('hakakses')->insert($rows);

            DB::commit();

            return redirect()->route('hakakses.index')->with('success', 'Hak akses berhasil disimpan');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->route('hakakses.index')->with('error', 'Gagal menyimpan hak akses: '.$e->getMessage());
        }
    }

    public function trash()
    {
        $system = DB::table('system')->first();
        if (session('level') != 1) {
            return redirect('/home')->with('error', 'Access denied');
        }

        $role = session('role');
        $groupKey = $role === 'Superadmin' ? 'superadmin' : 'admin';
        $allowed = false;
        try {
            $allowed = (bool) DB::table('hakakses')
                ->where('group_key', $groupKey)
                ->where('menu_key', 'trash')
                ->value('allowed');
        } catch (\Throwable $e) {
            $allowed = false;
        }

        if (! $allowed) {
            return redirect('/home')->with('error', 'Access denied');
        }

        $logs = DB::table('trash_logs')
            ->where('entity_type', 'course')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        echo view('all.header', compact('system'));
        echo view('all.menu', compact('system'));
        echo view('superadmin.trash', compact('logs'));
        echo view('all.footer');
    }

    public function trashRestore(Request $request)
    {
        if (session('level') != 1) {
            return redirect('/home')->with('error', 'Access denied');
        }

        $request->validate([
            'log_id' => 'required|integer',
        ]);

        $log = DB::table('trash_logs')->where('id', $request->log_id)->first();
        if (! $log) {
            return back()->with('error', 'Log tidak ditemukan');
        }

        if ($log->entity_type !== 'course') {
            return back()->with('error', 'Tipe data tidak didukung');
        }

        $before = [];
        if (! empty($log->before_json)) {
            $decoded = json_decode($log->before_json, true);
            if (is_array($decoded)) {
                $before = $decoded;
            }
        }

        $coursename = (string) ($before['coursename'] ?? '');
        if ($coursename === '') {
            return back()->with('error', 'Data restore tidak valid');
        }

        DB::beginTransaction();
        try {
            if ($log->action === 'update') {
                DB::table('course')->where('courseid', $log->entity_id)->update([
                    'coursename' => $coursename,
                ]);
            } elseif ($log->action === 'delete') {
                $exists = DB::table('course')->where('courseid', $log->entity_id)->exists();
                if ($exists) {
                    DB::table('course')->where('courseid', $log->entity_id)->update([
                        'coursename' => $coursename,
                        'deleted_at' => null,
                    ]);
                } else {
                    DB::table('course')->insert([
                        'courseid' => $log->entity_id,
                        'coursename' => $coursename,
                        'deleted_at' => null,
                    ]);
                }
            } else {
                DB::rollBack();

                return back()->with('error', 'Aksi tidak didukung');
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal restore');
        }

        return back()->with('success', 'Berhasil restore');
    }

    public function trashDelete(Request $request)
    {
        if (session('level') != 1) {
            return redirect('/home')->with('error', 'Access denied');
        }

        $request->validate([
            'log_id' => 'required|integer',
        ]);

        $log = DB::table('trash_logs')->where('id', $request->log_id)->first();
        if (! $log) {
            return back()->with('error', 'Log tidak ditemukan');
        }

        if ($log->entity_type !== 'course') {
            return back()->with('error', 'Tipe data tidak didukung');
        }

        if ($log->action === 'update') {
            DB::table('trash_logs')->where('id', $log->id)->delete();

            return back()->with('success', 'Log edit dihapus');
        }

        if ($log->action === 'delete') {
            $course = DB::table('course')->where('courseid', $log->entity_id)->first();
            if (! $course) {
                DB::table('trash_logs')->where('id', $log->id)->delete();

                return back()->with('success', 'Log hapus dihapus');
            }

            if ($course->deleted_at === null) {
                DB::table('trash_logs')->where('id', $log->id)->delete();

                return back()->with('success', 'Log hapus dihapus');
            }

            $used = DB::table('schedule')->where('courseid', $log->entity_id)->count();
            if ($used > 0) {
                return back()->with('error', 'Tidak bisa hapus permanen: masih dipakai jadwal');
            }

            DB::beginTransaction();
            try {
                DB::table('course')->where('courseid', $log->entity_id)->delete();
                DB::table('trash_logs')->where('id', $log->id)->delete();
                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();

                return back()->with('error', 'Gagal hapus permanen');
            }

            return back()->with('success', 'Data berhasil dihapus permanen');
        }

        return back()->with('error', 'Aksi tidak didukung');
    }

    public function activityLog()
    {
        $system = DB::table('system')->first();
        if (session('level') != 1) { // Only admin/superadmin
            return redirect('/home')->with('error', 'Access denied');
        }

        $logs = DB::table('activity_logs')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        echo view('all.header', compact('system'));
        echo view('all.menu', compact('system'));
        echo view('superadmin.activity_log', compact('logs'));
        echo view('all.footer');
    }

    public function allcourse()
    {
        $system = DB::table('system')->first();
        $course = DB::table('course')->whereNull('deleted_at')->get();
        echo view('all.header', compact('system'));
        echo view('all.menu', compact('system'));
        echo view('all.allcourse', compact('course'));
        echo view('all.footer');
    }

    public function savecourse(Request $request)
    {
        $request->validate([
            'coursename' => 'required',
        ]);

        DB::table('course')->insert([
            'coursename' => $request->coursename,
        ]);

        return back()->with('success', 'Mata Pelajaran berhasil ditambahkan');
    }

    public function updatecourse(Request $request)
    {
        $request->validate([
            'courseid' => 'required',
            'coursename' => 'required',
        ]);

        DB::beginTransaction();
        try {
            $before = DB::table('course')->where('courseid', $request->courseid)->first();
            if (! $before) {
                DB::rollBack();

                return back()->with('error', 'Mata Pelajaran tidak ditemukan');
            }

            DB::table('course')->where('courseid', $request->courseid)->update([
                'coursename' => $request->coursename,
            ]);

            $user = DB::table('user')->where('userid', session('userid'))->first();
            DB::table('trash_logs')->insert([
                'entity_type' => 'course',
                'entity_id' => (int) $request->courseid,
                'action' => 'update',
                'before_json' => json_encode(['coursename' => $before->coursename], JSON_UNESCAPED_UNICODE),
                'after_json' => json_encode(['coursename' => $request->coursename], JSON_UNESCAPED_UNICODE),
                'performed_userid' => session('userid') ? (int) session('userid') : null,
                'performed_username' => $user->username ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal memperbarui mata pelajaran');
        }

        return back()->with('success', 'Mata Pelajaran berhasil diperbarui');
    }

    public function deletecourse($id)
    {
        DB::beginTransaction();
        try {
            $before = DB::table('course')->where('courseid', $id)->first();
            if (! $before) {
                DB::rollBack();

                return back()->with('error', 'Mata Pelajaran tidak ditemukan');
            }

            DB::table('course')->where('courseid', $id)->update([
                'deleted_at' => now(),
            ]);

            $user = DB::table('user')->where('userid', session('userid'))->first();
            DB::table('trash_logs')->insert([
                'entity_type' => 'course',
                'entity_id' => (int) $id,
                'action' => 'delete',
                'before_json' => json_encode(['coursename' => $before->coursename], JSON_UNESCAPED_UNICODE),
                'after_json' => null,
                'performed_userid' => session('userid') ? (int) session('userid') : null,
                'performed_username' => $user->username ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menghapus mata pelajaran');
        }

        return back()->with('success', 'Mata Pelajaran berhasil dihapus');
    }

    public function allclass()
    {
        $system = DB::table('system')->first();
        $class = DB::table('class')->get();
        echo view('all.header', compact('system'));
        echo view('all.menu', compact('system'));
        echo view('all.allclass', compact('class'));
        echo view('all.footer');
    }

    public function saveclass(Request $request)
    {
        $request->validate([
            'classname' => 'required',
        ]);

        DB::table('class')->insert([
            'classname' => $request->classname,
        ]);

        return back()->with('success', 'Kelas berhasil ditambahkan');
    }

    public function updateclass(Request $request)
    {
        $request->validate([
            'classid' => 'required',
            'classname' => 'required',
        ]);

        DB::table('class')->where('classid', $request->classid)->update([
            'classname' => $request->classname,
        ]);

        return back()->with('success', 'Kelas berhasil diperbarui');
    }

    public function deleteclass($id)
    {
        DB::table('class')->where('classid', $id)->delete();

        return back()->with('success', 'Kelas berhasil dihapus');
    }

    public function allblock()
    {
        $system = DB::table('system')->first();
        $block = DB::table('block')
            ->join('academic_year', 'block.academic_year_id', '=', 'academic_year.academic_year_id')
            ->select('block.*', 'academic_year.name as academic_year_name')
            ->get();
        $years = DB::table('academic_year')->get();
        echo view('all.header', compact('system'));
        echo view('all.menu', compact('system'));
        echo view('all.allblock', compact('block', 'years'));
        echo view('all.footer');
    }

    public function saveblock(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required',
            'name' => 'required',
            'date_start' => 'required|date',
            'date_end' => 'required|date',
        ]);

        DB::table('block')->insert([
            'academic_year_id' => $request->academic_year_id,
            'name' => $request->name,
            'date_start' => $request->date_start,
            'date_end' => $request->date_end,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Blok berhasil ditambahkan');
    }

    public function updateblock(Request $request)
    {
        $request->validate([
            'block_id' => 'required',
            'academic_year_id' => 'required',
            'name' => 'required',
            'date_start' => 'required|date',
            'date_end' => 'required|date',
        ]);

        DB::table('block')->where('block_id', $request->block_id)->update([
            'academic_year_id' => $request->academic_year_id,
            'name' => $request->name,
            'date_start' => $request->date_start,
            'date_end' => $request->date_end,
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Blok berhasil diperbarui');
    }

    public function deleteblock($id)
    {
        DB::table('block')->where('block_id', $id)->delete();

        return back()->with('success', 'Blok berhasil dihapus');
    }

    public function allacademicyear()
    {
        $system = DB::table('system')->first();
        $academic_year = DB::table('academic_year')->get();
        echo view('all.header', compact('system'));
        echo view('all.menu', compact('system'));
        echo view('all.allacademicyear', compact('academic_year'));
        echo view('all.footer');
    }

    public function saveacademicyear(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'is_active' => 'required|boolean',
        ]);

        if ($request->is_active) {
            // Deactivate other years if this one is active
            DB::table('academic_year')->update(['is_active' => 0]);
        }

        DB::table('academic_year')->insert([
            'name' => $request->name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'is_active' => $request->is_active,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Tahun Ajaran berhasil ditambahkan');
    }

    public function updateacademicyear(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required',
            'name' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'is_active' => 'required|boolean',
        ]);

        if ($request->is_active) {
            // Deactivate other years if this one is active
            DB::table('academic_year')->where('academic_year_id', '!=', $request->academic_year_id)->update(['is_active' => 0]);
        }

        DB::table('academic_year')->where('academic_year_id', $request->academic_year_id)->update([
            'name' => $request->name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'is_active' => $request->is_active,
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Tahun Ajaran berhasil diperbarui');
    }

    public function deleteacademicyear($id)
    {
        DB::table('academic_year')->where('academic_year_id', $id)->delete();

        return back()->with('success', 'Tahun Ajaran berhasil dihapus');
    }

    // ==========================================================================================

    public function login(Request $request)
    {
        $system = DB::table('system')->first();

        $username = old('username', $request->session()->get('last_login_username'));
        $failCount = 0;
        if (! empty($username)) {
            $failCount = $this->loginFailCount($this->loginFailKey($request->ip(), $username));
        }

        $captchaRequired = (bool) session('captcha_required') || $failCount >= self::LOGIN_MAX_ATTEMPTS;
        $offlineQuestion = null;
        if ($captchaRequired) {
            $a = random_int(1, 20);
            $b = random_int(1, 20);
            $offlineQuestion = "{$a} + {$b}";
            $request->session()->put('offline_captcha_answer', $a + $b);
        }

        $recaptchaSiteKey = env('RECAPTCHA_SITE_KEY');

        echo view('all.header', compact('system'));
        echo view('all.login', compact('system', 'captchaRequired', 'offlineQuestion', 'recaptchaSiteKey'));
        echo view('all.footer');
    }

    public function loginact(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $username = trim((string) $request->username);
        $ip = $request->ip();
        $failKey = $this->loginFailKey($ip, $username);
        $failCount = $this->loginFailCount($failKey);
        $captchaRequired = $failCount >= self::LOGIN_MAX_ATTEMPTS;

        if ($captchaRequired) {
            $captchaOk = false;
            $secret = env('RECAPTCHA_SECRET_KEY');
            $token = $request->input('g-recaptcha-response');

            if (! empty($secret) && ! empty($token)) {
                try {
                    $resp = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                        'secret' => $secret,
                        'response' => $token,
                        'remoteip' => $ip,
                    ]);

                    if ($resp->ok() && (bool) ($resp->json('success') ?? false)) {
                        $captchaOk = true;
                    }
                } catch (\Throwable $e) {
                }
            }

            if (! $captchaOk) {
                $expected = $request->session()->get('offline_captcha_answer');
                $answer = $request->input('captcha_answer');
                if ($expected !== null && is_numeric($answer) && (int) $answer === (int) $expected) {
                    $captchaOk = true;
                }
            }

            if (! $captchaOk) {
                $this->incrementLoginFail($failKey);
                $request->session()->put('last_login_username', $username);

                return back()
                    ->withInput()
                    ->with('captcha_required', true)
                    ->with('error', 'Captcha salah atau belum diisi');
            }
        }

        $user = DB::table('user')
            ->where('username', $username)
            ->first();

        if (! $user) {
            $newCount = $this->incrementLoginFail($failKey);
            $request->session()->put('last_login_username', $username);

            return back()
                ->withInput()
                ->with('captcha_required', $newCount >= self::LOGIN_MAX_ATTEMPTS)
                ->with('error', 'Username tidak ada');
        }

        if (! Hash::check($request->password, $user->password)) {
            $newCount = $this->incrementLoginFail($failKey);
            $request->session()->put('last_login_username', $username);

            return back()
                ->withInput()
                ->with('captcha_required', $newCount >= self::LOGIN_MAX_ATTEMPTS)
                ->with('error', 'Salah Password');
        }

        $name = null;
        $email = null;
        $phonenumber = null;
        $role = null;

        if ($user->levelid == 3) {
            $data = DB::table('student')->where('userid', $user->userid)->first();

            if ($data) {
                $name = $data->name;
                $email = $data->email;
                $phonenumber = $data->phonenumber;
            }

        } elseif ($user->levelid == 1) {
            $data = DB::table('employer')
                ->leftJoin('role', 'role.roleid', '=', 'employer.roleid')
                ->where('employer.userid', $user->userid)
                ->select('employer.*', 'role.rolename')
                ->first();

            if ($data) {
                $name = $data->name;
                $email = $data->email;
                $phonenumber = $data->phonenumber;
                $role = $data->rolename;
            }

        } else {
            $data = DB::table('teacher')
                ->leftJoin('role', 'role.roleid', '=', 'teacher.roleid')
                ->where('teacher.userid', $user->userid)
                ->select('teacher.*', 'role.rolename')
                ->first();

            if ($data) {
                $name = $data->name;
                $email = $data->email;
                $phonenumber = $data->phonenumber;
                $role = $data->rolename;
            }
        }

        Session::put([
            'userid' => $user->userid,
            'username' => $user->username,
            'level' => $user->levelid,
            'name' => $name,
            'email' => $email,
            'phonenumber' => $phonenumber,
            'role' => $role,
            'is_login' => true,
        ]);

        $this->clearLoginFail($failKey);
        $request->session()->forget('offline_captcha_answer');
        $request->session()->forget('last_login_username');

        ActivityLogger::log('Login', $request->ip());

        return redirect('/home')->with('success', 'Login berhasil');
    }

    public function logout(Request $request)
    {
        ActivityLogger::log('Logout', $request->ip());

        $userid = $request->session()->get('userid');

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/home');
    }

    // ==============================================================================================
    public function forgetemail()
    {
        echo view('all.header', compact('system'));
        echo view('all.forgetpasswordemail');
        echo view('all.footer');
    }

    // ==============================================================================================

    public function home()
    {
        $system = DB::table('system')->first();

        $assignments = [];
        $userid = session('userid');
        $user = DB::table('user')->where('userid', $userid)->first();

        // Data for Cards
        $card1_value = 0;
        $card1_label = '';
        $card2_value = 0;
        $card2_label = '';
        $card3_value = 0;
        $card3_label = '';

        // Logic for Card 1 (Total Users - only for Superadmin/Admin)
        if ($user && $user->levelid == 1) { // Employer (Superadmin/Admin)
            $card1_value = DB::table('user')->count();
            $card1_label = 'Total User';
        }

        if ($user) {
            if ($user->levelid == 3) { // Student
                $student = DB::table('student')->where('userid', $userid)->first();
                if ($student) {
                    // Assignments for Calendar
                    $assignments = DB::table('assignment')
                        ->join('schedule', 'assignment.scheduleid', '=', 'schedule.scheduleid')
                        ->where('schedule.classid', $student->classid)
                        ->select('assignment.name', 'assignment.time_end')
                        ->get();

                    // Card 2: Total tugas siswa yang belum dikerjakan
                    $totalAssignments = DB::table('assignment')
                        ->join('schedule', 'assignment.scheduleid', '=', 'schedule.scheduleid')
                        ->where('schedule.classid', $student->classid)
                        ->count();

                    $submittedAssignments = DB::table('quest')
                        ->where('studentid', $student->studentid)
                        ->distinct('assignmentid')
                        ->count();

                    $card2_value = $totalAssignments - $submittedAssignments;
                    $card2_label = 'Tugas Belum Dikerjakan';

                    // Card 3: Total tugas yang sudah dikerjakan
                    $card3_value = $submittedAssignments;
                    $card3_label = 'Tugas Sudah Dikerjakan';
                }
            } elseif ($user->levelid == 2) { // Teacher or Curriculum
                $teacher = DB::table('teacher')->where('userid', $userid)->first();
                if ($teacher) {
                    if ($teacher->roleid == 4) { // Curriculum (assuming roleid 4 is Curriculum based on previous context)
                        // Card 2: Total tugas (semua)
                        $card2_value = DB::table('assignment')->count();
                        $card2_label = 'Total Semua Tugas';

                        // Card 3: Total tugas yang dikumpulkan (semua)
                        $card3_value = DB::table('quest')->count();
                        $card3_label = 'Total Pengumpulan Tugas';

                    } else { // Regular Teacher
                        // Assignments for Calendar
                        $assignments = DB::table('assignment')
                            ->join('schedule', 'assignment.scheduleid', '=', 'schedule.scheduleid')
                            ->where('schedule.teacherid', $teacher->teacherid)
                            ->select('assignment.name', 'assignment.time_end')
                            ->get();

                        // Card 2: Total tugas yang dibuat
                        $card2_value = DB::table('assignment')
                            ->join('schedule', 'assignment.scheduleid', '=', 'schedule.scheduleid')
                            ->where('schedule.teacherid', $teacher->teacherid)
                            ->count();
                        $card2_label = 'Tugas Dibuat';

                        // Card 3: Total tugas yang dikumpulkan/dinilai
                        // Count unique submissions for assignments created by this teacher
                        $card3_value = DB::table('quest')
                            ->join('assignment', 'quest.assignmentid', '=', 'assignment.assignmentid')
                            ->join('schedule', 'assignment.scheduleid', '=', 'schedule.scheduleid')
                            ->where('schedule.teacherid', $teacher->teacherid)
                            ->count();
                        $card3_label = 'Tugas Masuk';
                    }
                }
            } elseif ($user->levelid == 1) { // Admin/Superadmin
                // Card 2: Total tugas di database
                $card2_value = DB::table('assignment')->count();
                $card2_label = 'Total Tugas Database';

                // Card 3: Not visible for admin/superadmin based on request
                $card3_value = 0;
            }
        }

        $events = [];
        foreach ($assignments as $a) {
            $events[] = [
                'title' => 'Deadline: '.$a->name,
                'start' => date('Y-m-d', strtotime($a->time_end)),
                'color' => '#dc3545', // Red color for deadline
                'allDay' => true,
            ];
        }

        echo view('all.header', compact('system'));
        echo view('all.menu', compact('system'));
        echo view('all.home', compact('system', 'events', 'card1_value', 'card1_label', 'card2_value', 'card2_label', 'card3_value', 'card3_label', 'user'));
        echo view('all.footer');
    }

    // ===========================================================================================

    public function profile()
    {
        $system = DB::table('system')->first();
        $user = DB::table('user')->where('userid', session('userid'))->first();
        $data = null;
        if ($user) {
            if ($user->levelid == 3) {
                $data = DB::table('student')
                    ->leftJoin('class', 'class.classid', '=', 'student.classid')
                    ->where('student.userid', $user->userid)
                    ->select('student.*', 'class.classname')
                    ->first();
            } elseif ($user->levelid == 1) {
                $data = DB::table('employer')->where('userid', $user->userid)->first();
            } else {
                $data = DB::table('teacher')->where('userid', $user->userid)->first();
            }
        }
        echo view('all.header', compact('system'));
        echo view('all.menu', compact('system'));
        echo view('all.profile', compact('user', 'data'));
        echo view('all.footer');
    }

    public function updateprofile(Request $request)
    {
        $user = DB::table('user')->where('userid', session('userid'))->first();
        if (! $user) {
            return back()->with('error', 'User tidak ditemukan');
        }

        $request->validate([
            'name' => 'required',
        ]);

        if ($user->levelid == 3) {
            DB::table('student')->where('userid', $user->userid)->update([
                'name' => $request->name,
            ]);
        } elseif ($user->levelid == 1) {
            DB::table('employer')->where('userid', $user->userid)->update([
                'name' => $request->name,
            ]);
        } else {
            DB::table('teacher')->where('userid', $user->userid)->update([
                'name' => $request->name,
            ]);
        }

        Session::put([
            'name' => $request->name,
        ]);

        ActivityLogger::log("Update Profil: Mengubah nama menjadi {$request->name}", $request->ip());

        return back()->with('success', 'Profil berhasil diperbarui');
    }

    public function updatepassword(Request $request)
    {
        $user = DB::table('user')->where('userid', session('userid'))->first();
        if (! $user) {
            return back()->with('error', 'User tidak ditemukan');
        }

        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|min:6',
        ]);

        if (! Hash::check($request->old_password, $user->password)) {
            return back()->with('error', 'Password lama salah');
        }

        DB::table('user')->where('userid', $user->userid)->update([
            'password' => Hash::make($request->new_password),
        ]);

        return back()->with('success', 'Password berhasil diperbarui');
    }

    public function requestEmailChange(Request $request)
    {
        $user = DB::table('user')->where('userid', session('userid'))->first();
        if (! $user) {
            return back()->with('error', 'User tidak ditemukan');
        }

        $request->validate([
            'new_email' => 'required|email',
        ]);

        // Check uniqueness manually across tables because strict unique rule might fail if user uses their own email? No, unique:table,col is fine.
        // But let's just use the rule.
        // Wait, if I use unique:student,email, it checks against all students.
        $exists = DB::table('student')->where('email', $request->new_email)->exists() ||
                  DB::table('teacher')->where('email', $request->new_email)->exists() ||
                  DB::table('employer')->where('email', $request->new_email)->exists();

        if ($exists) {
            return back()->with('error', 'Email sudah digunakan oleh user lain.');
        }

        $token = Str::random(60);

        DB::table('user')->where('userid', $user->userid)->update([
            'pending_email' => $request->new_email,
            'email_verification_token' => $token,
        ]);

        // Send Email
        $link = route('profile.email.verify', ['token' => $token]);
        $name = session('name');

        try {
            Mail::send('emails.change_email', ['name' => $name, 'link' => $link], function ($message) use ($request) {
                $message->to($request->new_email);
                $message->subject('Verifikasi Perubahan Email');
            });
        } catch (\Exception $e) {
            // For dev environment without mail setup, log the link
            \Illuminate\Support\Facades\Log::info('Email Verification Link: '.$link);

            return back()->with('error', 'Gagal mengirim email (Cek Log untuk Link di Dev): '.$e->getMessage());
        }

        return back()->with('success', 'Link verifikasi telah dikirim ke email baru Anda. Silakan cek inbox/spam.');
    }

    public function verifyEmailChange($token)
    {
        $user = DB::table('user')->where('email_verification_token', $token)->first();

        if (! $user) {
            return redirect()->route('profile')->with('error', 'Link verifikasi tidak valid atau sudah kadaluarsa.');
        }

        // Update email in the specific role table
        if ($user->levelid == 3) {
            DB::table('student')->where('userid', $user->userid)->update(['email' => $user->pending_email]);
        } elseif ($user->levelid == 1) {
            DB::table('employer')->where('userid', $user->userid)->update(['email' => $user->pending_email]);
        } else {
            DB::table('teacher')->where('userid', $user->userid)->update(['email' => $user->pending_email]);
        }

        // Update session
        Session::put('email', $user->pending_email);

        // Clear pending data
        DB::table('user')->where('userid', $user->userid)->update([
            'pending_email' => null,
            'email_verification_token' => null,
        ]);

        return redirect()->route('profile')->with('success', 'Email berhasil diubah!');
    }

    public function requestPhoneChange(Request $request)
    {
        $user = DB::table('user')->where('userid', session('userid'))->first();
        if (! $user) {
            return back()->with('error', 'User tidak ditemukan');
        }

        $request->validate([
            'new_phone' => 'required|numeric',
        ]);

        $exists = DB::table('student')->where('phonenumber', $request->new_phone)->exists() ||
                  DB::table('teacher')->where('phonenumber', $request->new_phone)->exists() ||
                  DB::table('employer')->where('phonenumber', $request->new_phone)->exists();

        if ($exists) {
            return back()->with('error', 'Nomor telepon sudah digunakan oleh user lain.');
        }

        $otp = rand(100000, 999999);

        DB::table('user')->where('userid', $user->userid)->update([
            'pending_phone' => $request->new_phone,
            'phone_otp' => $otp,
            'phone_otp_expires_at' => now()->addMinutes(10),
        ]);

        try {
            $response = Http::withHeaders([
                'Authorization' => env('FONNTE_TOKEN'),
            ])->post('https://api.fonnte.com/send', [
                'target' => $request->new_phone,
                'message' => "Kode OTP Anda adalah: *$otp*\n\nKode ini berlaku selama 10 menit. Jangan berikan kode ini kepada siapapun.",
            ]);

            if ($response->failed()) {
                return back()->with('error', 'Gagal mengirim OTP via WhatsApp. Silakan coba lagi nanti.');
            }

            return back()->with('success', 'OTP telah dikirim ke nomor WhatsApp baru Anda.');

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat mengirim OTP: '.$e->getMessage());
        }
    }

    public function verifyPhoneChange(Request $request)
    {
        $user = DB::table('user')->where('userid', session('userid'))->first();
        if (! $user) {
            return back()->with('error', 'User tidak ditemukan');
        }

        $request->validate([
            'otp' => 'required|numeric',
        ]);

        if ($user->phone_otp != $request->otp) {
            return back()->with('error', 'OTP salah.');
        }

        if (now()->gt($user->phone_otp_expires_at)) {
            return back()->with('error', 'OTP sudah kadaluarsa. Silakan minta ulang.');
        }

        // Update phone
        if ($user->levelid == 3) {
            DB::table('student')->where('userid', $user->userid)->update(['phonenumber' => $user->pending_phone]);
        } elseif ($user->levelid == 1) {
            DB::table('employer')->where('userid', $user->userid)->update(['phonenumber' => $user->pending_phone]);
        } else {
            DB::table('teacher')->where('userid', $user->userid)->update(['phonenumber' => $user->pending_phone]);
        }

        // Update session
        Session::put('phonenumber', $user->pending_phone);

        // Clear pending data
        DB::table('user')->where('userid', $user->userid)->update([
            'pending_phone' => null,
            'phone_otp' => null,
            'phone_otp_expires_at' => null,
        ]);

        return back()->with('success', 'Nomor telepon berhasil diubah!');
    }

    public function cancelPhoneChange()
    {
        $user = DB::table('user')->where('userid', session('userid'))->first();
        if (! $user) {
            return back()->with('error', 'User tidak ditemukan');
        }

        DB::table('user')->where('userid', $user->userid)->update([
            'pending_phone' => null,
            'phone_otp' => null,
            'phone_otp_expires_at' => null,
        ]);

        return back()->with('success', 'Permintaan ganti nomor dibatalkan.');
    }
    // =====================================================================================

    public function userdata()
    {
        $data = DB::table('user')
            ->leftjoin('level', 'level.levelid', '=', 'user.levelid')
            ->leftjoin('employer', 'employer.userid', '=', 'user.userid')
            ->leftjoin('teacher', 'teacher.userid', '=', 'user.userid')
            ->leftjoin('student', 'student.userid', '=', 'user.userid')
            ->leftjoin('class', 'class.classid', '=', 'student.classid')
            ->leftJoin('role', function ($join) {
                $join->on('role.roleid', '=', 'employer.roleid')
                    ->orOn('role.roleid', '=', 'teacher.roleid');
            })
            ->select(
                'user.userid',
                'user.username',
                'level.levelname',
                'role.rolename',
                'class.classname',

                DB::raw('COALESCE(teacher.email, employer.email,student.email) as email'),
                DB::raw('COALESCE(teacher.phonenumber, employer.phonenumber,student.phonenumber) as phonenumber'),
                DB::raw('COALESCE(teacher.name, employer.name,student.name) as name'),
            )
            ->get();
        $system = DB::table('system')->first();
        $level = DB::table('level')->get();
        $role = DB::table('role')->get();
        $classes = DB::table('class')->orderBy('classname')->get();
        echo view('all.header', compact('system'));
        echo view('all.menu', compact('system'));
        echo view('admin.userdata', compact('data', 'level', 'role', 'classes'));
        echo view('all.footer');
    }

    public function saveuser(Request $request)
    {
        $rules = [
            'name' => 'required',
            'username' => 'required|unique:user,username',
            'email' => 'required|unique:student,email|unique:employer,email|unique:teacher,email',
            'phonenumber' => 'required|unique:student,phonenumber|unique:employer,phonenumber|unique:teacher,phonenumber',
            'level' => 'required',
        ];
        if ($request->level == 3) {
            $rules['role'] = 'nullable';
            $rules['classid'] = 'required|exists:class,classid';
        } else {
            $rules['role'] = 'required';
            $rules['classid'] = 'nullable';
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please fix the errors below');
        }

        DB::beginTransaction();

        try {
            $userid = DB::table('user')->insertGetId([
                'username' => $request->username,
                'password' => Hash::make($request->username), // default password
                'levelid' => $request->level,
            ]);

            if ($request->level == 3) {
                DB::table('student')->insert([
                    'name' => $request->name,
                    'email' => $request->email,
                    'phonenumber' => $request->phonenumber,
                    'classid' => $request->classid,
                    'userid' => $userid,
                ]);
            } elseif ($request->level == 1) {
                DB::table('employer')->insert([
                    'name' => $request->name,
                    'email' => $request->email,
                    'phonenumber' => $request->phonenumber,
                    'roleid' => $request->role,
                    'userid' => $userid,
                ]);
            } else {
                DB::table('teacher')->insert([
                    'name' => $request->name,
                    'email' => $request->email,
                    'phonenumber' => $request->phonenumber,
                    'roleid' => $request->role,
                    'userid' => $userid,
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', 'User successfully added');

        } catch (\Exception $e) {
            DB::rollback();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to add user: '.$e->getMessage());
        }
    }

    public function deleteuser($id)
    {
        $user = DB::table('user')
            ->where('userid', $id)
            ->first();

        $student = DB::table('student')
            ->where('userid', $id)
            ->first();

        $employer = DB::table('employer')
            ->where('userid', $id)
            ->first();

        $teacher = DB::table('teacher')
            ->where('userid', $id)
            ->first();

        DB::table('user')
            ->where('userid', $id)
            ->delete();

        DB::table('student')
            ->where('userid', $id)
            ->delete();

        DB::table('employer')
            ->where('userid', $id)
            ->delete();

        DB::table('teacher')
            ->where('userid', $id)
            ->delete();

        return back();
    }

    public function userresetpassword($id)
    {
        $user = DB::table('user')->where('userid', $id)->first();

        DB::table('user')->where('userid', $id)->update([
            'password' => Hash::make('12345'),
        ]);

        return back();
    }

    // ====================================================================================
    public function setting()
    {
        $system = DB::table('system')->first();
        echo view('all.header', compact('system'));
        echo view('all.menu', compact('system'));
        echo view('superadmin.setting', compact('system'));
        echo view('all.footer');
    }

    public function savesetting(Request $request)
    {
        $old = DB::table('system')
            ->where('systemid', $request->systemid)
            ->first();

        $request->validate([
            'name' => 'required',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'address' => 'required',
            'manager' => 'required',
            'contact' => 'required',
        ]);

        if ($request->hasFile('logo')) {
            if ($old && $old->systemlogo) {
                Storage::delete('public/'.$old->systemlogo);
            }
            $fotoPath = $request->file('logo')->store('uploads', 'public');
        } else {
            $fotoPath = $old->systemlogo; // pakai logo lama
        }

        $data = [
            'systemname' => $request->name,
            'systemlogo' => $fotoPath,
            'systemaddress' => $request->address,
            'systemmanager' => $request->manager,
            'systemcontact' => $request->contact,
        ];

        DB::table('system')
            ->where('systemid', $request->systemid)
            ->update($data);

        return redirect('/setting')->with('success', 'Setting succesafully updated');
    }

    // ========================================================================================================
    public function databasePage()
    {
        $system = DB::table('system')->first();

        echo view('all.header', compact('system'));
        echo view('all.menu', compact('system'));
        echo view('superadmin.database');
        echo view('all.footer');
    }

    public function exportDatabase()
    {
        $tables = array_map('current', DB::select('SHOW TABLES'));

        $sql = "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            $create = DB::select("SHOW CREATE TABLE `$table`")[0]->{'Create Table'};
            $sql .= "DROP TABLE IF EXISTS `$table`;\n";
            $sql .= $create.";\n\n";

            $rows = DB::table($table)->get();
            if ($rows->count() === 0) {
                continue;
            }

            foreach ($rows as $row) {
                $columns = array_keys((array) $row);
                $values = array_map(function ($value) {
                    if (is_null($value)) {
                        return 'NULL';
                    }

                    return "'".str_replace("'", "''", $value)."'";
                }, array_values((array) $row));

                $sql .= "REPLACE INTO `$table` (`".implode('`,`', $columns).'`) VALUES ('.implode(',', $values).");\n";
            }

            $sql .= "\n";
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        $filename = 'backup_'.date('Ymd_His').'.sql';

        return response($sql)
            ->header('Content-Type', 'application/sql')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
    }

    public function importDatabase(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file',
        ]);

        $path = $request->file('backup_file')->getRealPath();
        $contents = file_get_contents($path);

        $statements = array_filter(array_map('trim', explode(";\n", $contents)));

        DB::beginTransaction();
        try {
            foreach ($statements as $statement) {
                if ($statement === '' || strpos($statement, '--') === 0 || strpos($statement, '/*') === 0) {
                    continue;
                }
                DB::unprepared($statement.';');
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Database imported successfully');
    }
}
