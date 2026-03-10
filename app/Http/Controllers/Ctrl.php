<?php

namespace App\Http\Controllers;

use App\Helpers\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
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

    private function normalizeRoleLabel(?string $role): ?string
    {
        $value = mb_strtolower(trim((string) $role));
        if ($value === '') {
            return null;
        }
        $map = [
            'superadmin' => 'Superadmin',
            'admin' => 'Admin',
            'kurikulum' => 'Kurikulum',
            'curiculum' => 'Kurikulum',
            'guru' => 'Guru',
            'siswa' => 'Siswa',
        ];

        if (isset($map[$value])) {
            return $map[$value];
        }

        return ucfirst($value);
    }

    private function resolveClientIp(Request $request): string
    {
        $candidates = [
            $request->header('CF-Connecting-IP'),
            $request->header('X-Real-IP'),
            $request->header('X-Forwarded-For'),
            $request->server('REMOTE_ADDR'),
            $request->ip(),
        ];

        foreach ($candidates as $candidate) {
            if (empty($candidate)) {
                continue;
            }

            $ip = trim(explode(',', (string) $candidate)[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }

        return 'UNKNOWN';
    }

    private function resolveRoleByUserId(int $userid): ?string
    {
        $user = DB::table('user')->where('userid', $userid)->first();
        if (! $user) {
            return null;
        }

        if ((int) $user->levelid === 3) {
            return 'Siswa';
        }

        if ((int) $user->levelid === 1) {
            $employer = DB::table('employer')
                ->leftJoin('role', 'role.roleid', '=', 'employer.roleid')
                ->where('employer.userid', $userid)
                ->select('role.rolename')
                ->first();

            return $this->normalizeRoleLabel($employer->rolename ?? null);
        }

        if ((int) $user->levelid === 2) {
            $teacher = DB::table('teacher')
                ->leftJoin('role', 'role.roleid', '=', 'teacher.roleid')
                ->where('teacher.userid', $userid)
                ->select('role.rolename')
                ->first();

            return $this->normalizeRoleLabel($teacher->rolename ?? null);
        }

        return null;
    }

    private function insertTrashLog(Request $request, string $entityType, int $entityId, string $action, ?array $before, ?array $after): void
    {
        $userid = session('userid') ? (int) session('userid') : null;
        $username = session('username');
        if (empty($username) && $userid) {
            $user = DB::table('user')->where('userid', $userid)->select('username')->first();
            $username = $user->username ?? null;
        }

        $role = $this->normalizeRoleLabel((string) session('role'));
        if (empty($role) && $userid) {
            $role = $this->resolveRoleByUserId($userid);
        }

        $data = [
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action' => $action,
            'before_json' => $before !== null ? json_encode($before, JSON_UNESCAPED_UNICODE) : null,
            'after_json' => $after !== null ? json_encode($after, JSON_UNESCAPED_UNICODE) : null,
            'performed_userid' => $userid,
            'performed_username' => $username,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('trash_logs', 'performed_role')) {
            $data['performed_role'] = $role ?? '-';
        }
        if (Schema::hasColumn('trash_logs', 'performed_ip')) {
            $data['performed_ip'] = $this->resolveClientIp($request);
        }

        DB::table('trash_logs')->insert($data);
    }

    private function normalizePhoneNumber(string $phone): string
    {
        $raw = preg_replace('/\D+/', '', trim($phone));
        if ($raw === '') {
            return '';
        }
        if (str_starts_with($raw, '0')) {
            return '62'.substr($raw, 1);
        }
        if (str_starts_with($raw, '62')) {
            return $raw;
        }

        return $raw;
    }

    private function findUserByEmail(string $email): ?object
    {
        $student = DB::table('student')->whereRaw('LOWER(email) = ?', [mb_strtolower($email)])->first();
        if ($student) {
            return (object) ['userid' => (int) $student->userid, 'channel' => 'student', 'contact' => $student->email];
        }

        $teacher = DB::table('teacher')->whereRaw('LOWER(email) = ?', [mb_strtolower($email)])->first();
        if ($teacher) {
            return (object) ['userid' => (int) $teacher->userid, 'channel' => 'teacher', 'contact' => $teacher->email];
        }

        $employer = DB::table('employer')->whereRaw('LOWER(email) = ?', [mb_strtolower($email)])->first();
        if ($employer) {
            return (object) ['userid' => (int) $employer->userid, 'channel' => 'employer', 'contact' => $employer->email];
        }

        return null;
    }

    private function findUserByPhone(string $phone): ?object
    {
        $normalized = $this->normalizePhoneNumber($phone);
        if ($normalized === '') {
            return null;
        }
        $variants = array_values(array_unique(array_filter([
            $normalized,
            $phone,
            '+'.$normalized,
            str_starts_with($normalized, '62') ? '0'.substr($normalized, 2) : null,
        ])));

        $student = DB::table('student')->whereIn('phonenumber', $variants)->first();
        if ($student) {
            return (object) ['userid' => (int) $student->userid, 'channel' => 'student', 'contact' => $normalized];
        }

        $teacher = DB::table('teacher')->whereIn('phonenumber', $variants)->first();
        if ($teacher) {
            return (object) ['userid' => (int) $teacher->userid, 'channel' => 'teacher', 'contact' => $normalized];
        }

        $employer = DB::table('employer')->whereIn('phonenumber', $variants)->first();
        if ($employer) {
            return (object) ['userid' => (int) $employer->userid, 'channel' => 'employer', 'contact' => $normalized];
        }

        return null;
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

    public function trash(Request $request)
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

        $actionFilter = strtolower((string) $request->query('action_filter', 'all'));
        $roleFilter = strtolower((string) $request->query('role_filter', 'all'));
        $actionMap = [
            'all' => null,
            'edit' => 'update',
            'delete' => 'delete',
            'update' => 'update',
        ];
        $roleMap = [
            'all' => null,
            'admin' => 'Admin',
            'superadmin' => 'Superadmin',
            'kurikulum' => 'Kurikulum',
            'curiculum' => 'Kurikulum',
            'siswa' => 'Siswa',
            'guru' => 'Guru',
        ];
        $action = $actionMap[$actionFilter] ?? null;
        $roleKeyword = $roleMap[$roleFilter] ?? null;
        $hasRoleColumn = Schema::hasColumn('trash_logs', 'performed_role');
        $hasIpColumn = Schema::hasColumn('trash_logs', 'performed_ip');
        $derivedRoleSql = "CASE
            WHEN u.levelid = 3 THEN 'Siswa'
            WHEN u.levelid = 1 THEN emp_role.rolename
            WHEN u.levelid = 2 THEN tch_role.rolename
            ELSE NULL
        END";

        $logsQuery = DB::table('trash_logs')
            ->leftJoin('user as u', 'u.userid', '=', 'trash_logs.performed_userid')
            ->leftJoin('employer as emp', 'emp.userid', '=', 'u.userid')
            ->leftJoin('teacher as tch', 'tch.userid', '=', 'u.userid')
            ->leftJoin('role as emp_role', 'emp_role.roleid', '=', 'emp.roleid')
            ->leftJoin('role as tch_role', 'tch_role.roleid', '=', 'tch.roleid')
            ->whereIn('entity_type', ['course', 'class', 'academic_year', 'block'])
            ->select('trash_logs.*');

        if ($hasRoleColumn) {
            $logsQuery->selectRaw("COALESCE(NULLIF(TRIM(trash_logs.performed_role), ''), {$derivedRoleSql}, '-') as role_label");
        } else {
            $logsQuery->selectRaw("COALESCE({$derivedRoleSql}, '-') as role_label");
        }
        if ($hasIpColumn) {
            $logsQuery->selectRaw("COALESCE(NULLIF(TRIM(trash_logs.performed_ip), ''), 'UNKNOWN') as ip_label");
        } else {
            $logsQuery->selectRaw("'UNKNOWN' as ip_label");
        }

        if (! empty($action)) {
            $logsQuery->where('action', $action);
        }
        if (! empty($roleKeyword)) {
            if ($hasRoleColumn) {
                $logsQuery->whereRaw("LOWER(COALESCE(NULLIF(TRIM(trash_logs.performed_role), ''), {$derivedRoleSql}, '')) = ?", [strtolower($roleKeyword)]);
            } else {
                $logsQuery->whereRaw("LOWER(COALESCE({$derivedRoleSql}, '')) = ?", [strtolower($roleKeyword)]);
            }
        }

        $logs = $logsQuery
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->appends([
                'action_filter' => $actionFilter,
                'role_filter' => $roleFilter,
            ]);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('superadmin.partials.trash_table', compact('logs'))->render(),
            ]);
        }

        echo view('all.header', compact('system'));
        echo view('all.menu', compact('system'));
        echo view('superadmin.trash', compact('logs', 'actionFilter', 'roleFilter'));
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

        $before = [];
        if (! empty($log->before_json)) {
            $decoded = json_decode($log->before_json, true);
            if (is_array($decoded)) {
                $before = $decoded;
            }
        }

        DB::beginTransaction();
        try {
            if (! in_array($log->action, ['update', 'delete'], true)) {
                DB::rollBack();

                return back()->with('error', 'Aksi tidak didukung');
            }

            if ($log->entity_type === 'course') {
                $coursename = (string) ($before['coursename'] ?? '');
                if ($coursename === '') {
                    DB::rollBack();

                    return back()->with('error', 'Data restore course tidak valid');
                }

                if ($log->action === 'update') {
                    DB::table('course')->where('courseid', $log->entity_id)->update([
                        'coursename' => $coursename,
                    ]);
                } else {
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
                }
            } elseif ($log->entity_type === 'class') {
                $classname = (string) ($before['classname'] ?? '');
                if ($classname === '') {
                    DB::rollBack();

                    return back()->with('error', 'Data restore class tidak valid');
                }

                if ($log->action === 'update') {
                    DB::table('class')->where('classid', $log->entity_id)->update([
                        'classname' => $classname,
                    ]);
                } else {
                    $exists = DB::table('class')->where('classid', $log->entity_id)->exists();
                    if ($exists) {
                        DB::table('class')->where('classid', $log->entity_id)->update([
                            'classname' => $classname,
                        ]);
                    } else {
                        DB::table('class')->insert([
                            'classid' => $log->entity_id,
                            'classname' => $classname,
                        ]);
                    }
                }
            } elseif ($log->entity_type === 'academic_year') {
                $payload = [
                    'name' => (string) ($before['name'] ?? ''),
                    'start_date' => (string) ($before['start_date'] ?? ''),
                    'end_date' => (string) ($before['end_date'] ?? ''),
                    'is_active' => (int) ($before['is_active'] ?? 0),
                ];
                if ($payload['name'] === '' || $payload['start_date'] === '' || $payload['end_date'] === '') {
                    DB::rollBack();

                    return back()->with('error', 'Data restore tahun ajaran tidak valid');
                }

                if ($log->action === 'update') {
                    DB::table('academic_year')->where('academic_year_id', $log->entity_id)->update([
                        'name' => $payload['name'],
                        'start_date' => $payload['start_date'],
                        'end_date' => $payload['end_date'],
                        'is_active' => $payload['is_active'],
                        'updated_at' => now(),
                    ]);
                } else {
                    $exists = DB::table('academic_year')->where('academic_year_id', $log->entity_id)->exists();
                    if ($exists) {
                        DB::table('academic_year')->where('academic_year_id', $log->entity_id)->update([
                            'name' => $payload['name'],
                            'start_date' => $payload['start_date'],
                            'end_date' => $payload['end_date'],
                            'is_active' => $payload['is_active'],
                            'updated_at' => now(),
                        ]);
                    } else {
                        $insertData = $before;
                        $insertData['academic_year_id'] = $log->entity_id;
                        DB::table('academic_year')->insert($insertData);
                    }
                }
            } elseif ($log->entity_type === 'block') {
                $payload = [
                    'academic_year_id' => (int) ($before['academic_year_id'] ?? 0),
                    'name' => (string) ($before['name'] ?? ''),
                    'date_start' => (string) ($before['date_start'] ?? ''),
                    'date_end' => (string) ($before['date_end'] ?? ''),
                ];
                if ($payload['academic_year_id'] <= 0 || $payload['name'] === '' || $payload['date_start'] === '' || $payload['date_end'] === '') {
                    DB::rollBack();

                    return back()->with('error', 'Data restore blok tidak valid');
                }

                if ($log->action === 'update') {
                    DB::table('block')->where('block_id', $log->entity_id)->update([
                        'academic_year_id' => $payload['academic_year_id'],
                        'name' => $payload['name'],
                        'date_start' => $payload['date_start'],
                        'date_end' => $payload['date_end'],
                        'updated_at' => now(),
                    ]);
                } else {
                    $exists = DB::table('block')->where('block_id', $log->entity_id)->exists();
                    if ($exists) {
                        DB::table('block')->where('block_id', $log->entity_id)->update([
                            'academic_year_id' => $payload['academic_year_id'],
                            'name' => $payload['name'],
                            'date_start' => $payload['date_start'],
                            'date_end' => $payload['date_end'],
                            'updated_at' => now(),
                        ]);
                    } else {
                        $insertData = $before;
                        $insertData['block_id'] = $log->entity_id;
                        DB::table('block')->insert($insertData);
                    }
                }
            } else {
                DB::rollBack();

                return back()->with('error', 'Tipe data tidak didukung');
            }

            DB::table('trash_logs')->where('id', $log->id)->delete();
            ActivityLogger::log("Mengembalikan data {$log->entity_type} #{$log->entity_id}", $request->ip());

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

        if ($log->action === 'update') {
            DB::table('trash_logs')->where('id', $log->id)->delete();
            ActivityLogger::log("Menghapus log edit {$log->entity_type} #{$log->entity_id}", $request->ip());

            return back()->with('success', 'Log edit dihapus');
        }

        if ($log->action === 'delete' && $log->entity_type === 'course') {
            $course = DB::table('course')->where('courseid', $log->entity_id)->first();
            if (! $course) {
                DB::table('trash_logs')->where('id', $log->id)->delete();
                ActivityLogger::log("Menghapus log hapus course #{$log->entity_id}", $request->ip());

                return back()->with('success', 'Log hapus dihapus');
            }

            if ($course->deleted_at === null) {
                DB::table('trash_logs')->where('id', $log->id)->delete();
                ActivityLogger::log("Menghapus log hapus course #{$log->entity_id}", $request->ip());

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
                ActivityLogger::log("Menghapus permanen data course #{$log->entity_id}", $request->ip());
                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();

                return back()->with('error', 'Gagal hapus permanen');
            }

            return back()->with('success', 'Data berhasil dihapus permanen');
        }

        if ($log->action === 'delete') {
            DB::table('trash_logs')->where('id', $log->id)->delete();
            ActivityLogger::log("Menghapus log hapus {$log->entity_type} #{$log->entity_id}", $request->ip());

            return back()->with('success', 'Log hapus dihapus');
        }

        return back()->with('error', 'Aksi tidak didukung');
    }

    public function activityLog(Request $request)
    {
        $system = DB::table('system')->first();
        if (session('level') != 1) { // Only admin/superadmin
            return redirect('/home')->with('error', 'Access denied');
        }

        $sessionRole = strtolower(trim((string) session('role')));
        $canViewSuperadmin = $sessionRole === 'superadmin';

        $roleFilter = strtolower((string) $request->query('role_filter', 'all'));
        if (! $canViewSuperadmin && $roleFilter === 'superadmin') {
            $roleFilter = 'all';
        }
        $roleMap = [
            'all' => null,
            'admin' => 'Admin',
            'superadmin' => 'Superadmin',
            'kurikulum' => 'Kurikulum',
            'curiculum' => 'Kurikulum',
            'guru' => 'Guru',
            'siswa' => 'Siswa',
        ];
        $roleKeyword = $roleMap[$roleFilter] ?? null;

        $logsQuery = DB::table('activity_logs')
            ->where(function ($q) {
                $q->where('action', 'not like', 'GET %')
                    ->where('action', 'not like', 'POST %')
                    ->where('action', 'not like', 'PUT %')
                    ->where('action', 'not like', 'PATCH %')
                    ->where('action', 'not like', 'DELETE %');
            });

        if (! $canViewSuperadmin) {
            $logsQuery->whereRaw("LOWER(COALESCE(role, '')) <> 'superadmin'");
        }

        if (! empty($roleKeyword)) {
            $logsQuery->whereRaw('LOWER(role) = ?', [strtolower($roleKeyword)]);
        }

        $logs = $logsQuery
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->appends([
                'role_filter' => $roleFilter,
            ]);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('superadmin.partials.activity_log_table', compact('logs'))->render(),
            ]);
        }

        echo view('all.header', compact('system'));
        echo view('all.menu', compact('system'));
        echo view('superadmin.activity_log', compact('logs', 'roleFilter', 'canViewSuperadmin'));
        echo view('all.footer');
    }

    public function allcourse(Request $request)
    {
        $system = DB::table('system')->first();
        $course = DB::table('course')
            ->whereNull('deleted_at')
            ->orderBy('courseid', 'desc')
            ->paginate(10);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('all.partials.course_table', compact('course'))->render(),
            ]);
        }

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

            $this->insertTrashLog(
                $request,
                'course',
                (int) $request->courseid,
                'update',
                ['coursename' => $before->coursename],
                ['coursename' => $request->coursename]
            );
            ActivityLogger::log("Mengubah data mata pelajaran #{$request->courseid} dari {$before->coursename} menjadi {$request->coursename}", $request->ip());

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal memperbarui mata pelajaran');
        }

        return back()->with('success', 'Mata Pelajaran berhasil diperbarui');
    }

    public function deletecourse(Request $request, $id)
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

            $this->insertTrashLog(
                $request,
                'course',
                (int) $id,
                'delete',
                ['coursename' => $before->coursename],
                null
            );
            ActivityLogger::log("Menghapus data mata pelajaran #{$id} ({$before->coursename})", $request->ip());

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menghapus mata pelajaran');
        }

        return back()->with('success', 'Mata Pelajaran berhasil dihapus');
    }

    public function allclass(Request $request)
    {
        $system = DB::table('system')->first();
        $class = DB::table('class')
            ->orderBy('classid', 'desc')
            ->paginate(10);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('all.partials.class_table', compact('class'))->render(),
            ]);
        }

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

        DB::beginTransaction();
        try {
            $before = DB::table('class')->where('classid', $request->classid)->first();
            if (! $before) {
                DB::rollBack();

                return back()->with('error', 'Kelas tidak ditemukan');
            }

            DB::table('class')->where('classid', $request->classid)->update([
                'classname' => $request->classname,
            ]);

            $this->insertTrashLog(
                $request,
                'class',
                (int) $request->classid,
                'update',
                ['classname' => $before->classname],
                ['classname' => $request->classname]
            );
            ActivityLogger::log("Mengubah data kelas #{$request->classid} dari {$before->classname} menjadi {$request->classname}", $request->ip());

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal memperbarui kelas');
        }

        return back()->with('success', 'Kelas berhasil diperbarui');
    }

    public function deleteclass(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $before = DB::table('class')->where('classid', $id)->first();
            if (! $before) {
                DB::rollBack();

                return back()->with('error', 'Kelas tidak ditemukan');
            }

            DB::table('class')->where('classid', $id)->delete();

            $this->insertTrashLog(
                $request,
                'class',
                (int) $id,
                'delete',
                ['classid' => (int) $before->classid, 'classname' => $before->classname],
                null
            );
            ActivityLogger::log("Menghapus data kelas #{$id} ({$before->classname})", $request->ip());

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menghapus kelas');
        }

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

        DB::beginTransaction();
        try {
            $before = DB::table('block')->where('block_id', $request->block_id)->first();
            if (! $before) {
                DB::rollBack();

                return back()->with('error', 'Blok tidak ditemukan');
            }

            DB::table('block')->where('block_id', $request->block_id)->update([
                'academic_year_id' => $request->academic_year_id,
                'name' => $request->name,
                'date_start' => $request->date_start,
                'date_end' => $request->date_end,
                'updated_at' => now(),
            ]);

            $this->insertTrashLog(
                $request,
                'block',
                (int) $request->block_id,
                'update',
                [
                    'academic_year_id' => (int) $before->academic_year_id,
                    'name' => $before->name,
                    'date_start' => (string) $before->date_start,
                    'date_end' => (string) $before->date_end,
                ],
                [
                    'academic_year_id' => (int) $request->academic_year_id,
                    'name' => $request->name,
                    'date_start' => $request->date_start,
                    'date_end' => $request->date_end,
                ]
            );
            ActivityLogger::log("Mengubah data blok #{$request->block_id} dari {$before->name} menjadi {$request->name}", $request->ip());

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal memperbarui blok');
        }

        return back()->with('success', 'Blok berhasil diperbarui');
    }

    public function deleteblock(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $before = DB::table('block')->where('block_id', $id)->first();
            if (! $before) {
                DB::rollBack();

                return back()->with('error', 'Blok tidak ditemukan');
            }

            DB::table('block')->where('block_id', $id)->delete();

            $this->insertTrashLog(
                $request,
                'block',
                (int) $id,
                'delete',
                (array) $before,
                null
            );
            ActivityLogger::log("Menghapus data blok #{$id} ({$before->name})", $request->ip());

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menghapus blok');
        }

        return back()->with('success', 'Blok berhasil dihapus');
    }

    public function allacademicyear(Request $request)
    {
        $system = DB::table('system')->first();
        $academic_year = DB::table('academic_year')
            ->orderBy('academic_year_id', 'desc')
            ->paginate(10);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('all.partials.academic_year_table', compact('academic_year'))->render(),
            ]);
        }

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

        DB::beginTransaction();
        try {
            $before = DB::table('academic_year')->where('academic_year_id', $request->academic_year_id)->first();
            if (! $before) {
                DB::rollBack();

                return back()->with('error', 'Tahun ajaran tidak ditemukan');
            }

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

            $this->insertTrashLog(
                $request,
                'academic_year',
                (int) $request->academic_year_id,
                'update',
                [
                    'name' => $before->name,
                    'start_date' => (string) $before->start_date,
                    'end_date' => (string) $before->end_date,
                    'is_active' => (int) $before->is_active,
                ],
                [
                    'name' => $request->name,
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'is_active' => (int) $request->is_active,
                ]
            );
            ActivityLogger::log("Mengubah data tahun ajaran #{$request->academic_year_id} dari {$before->name} menjadi {$request->name}", $request->ip());

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal memperbarui tahun ajaran');
        }

        return back()->with('success', 'Tahun Ajaran berhasil diperbarui');
    }

    public function deleteacademicyear(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $before = DB::table('academic_year')->where('academic_year_id', $id)->first();
            if (! $before) {
                DB::rollBack();

                return back()->with('error', 'Tahun ajaran tidak ditemukan');
            }

            DB::table('academic_year')->where('academic_year_id', $id)->delete();

            $this->insertTrashLog(
                $request,
                'academic_year',
                (int) $id,
                'delete',
                (array) $before,
                null
            );
            ActivityLogger::log("Menghapus data tahun ajaran #{$id} ({$before->name})", $request->ip());

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menghapus tahun ajaran');
        }

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
        $teacherRoleId = null;

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
                $teacherRoleId = (int) ($data->roleid ?? 0);
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
            'teacher_roleid' => $teacherRoleId,
            'latitude' => $request->input('latitude'),
            'longitude' => $request->input('longitude'),
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
    public function forgotPasswordEmailPage()
    {
        $system = DB::table('system')->first();
        echo view('all.header', compact('system'));
        echo view('all.forgot_password_email');
        echo view('all.footer');
    }

    public function forgotPasswordPhonePage()
    {
        $system = DB::table('system')->first();
        $otpPhone = old('phone');
        if (empty($otpPhone)) {
            $otpPhone = session('otp_phone');
        }
        $showOtpForm = ! empty($otpPhone);
        echo view('all.header', compact('system'));
        echo view('all.forgot_password_phone', compact('showOtpForm', 'otpPhone'));
        echo view('all.footer');
    }

    public function forgotPasswordSendEmailLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = mb_strtolower(trim((string) $request->email));
        $found = $this->findUserByEmail($email);
        if (! $found) {
            return back()->with('error', 'Email tidak terdaftar di database.');
        }

        $token = Str::random(64);
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            ['token' => Hash::make($token), 'created_at' => now()]
        );

        $link = route('password.forgot.email.form', ['token' => $token, 'email' => $email]);

        try {
            Mail::send('emails.reset_password', ['link' => $link, 'email' => $email], function ($message) use ($email) {
                $message->to($email);
                $message->subject('Reset Password');
            });
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal mengirim email reset password.')->withInput();
        }

        return back()->with('success', 'Link reset password sudah dikirim ke email Anda.');
    }

    public function forgotPasswordEmailResetForm(Request $request)
    {
        $system = DB::table('system')->first();
        $email = mb_strtolower(trim((string) $request->query('email')));
        $token = (string) $request->query('token');
        if ($email === '' || $token === '') {
            return redirect()->route('password.forgot')->with('error', 'Link reset password tidak valid.');
        }

        echo view('all.header', compact('system'));
        echo view('all.reset_password_email', compact('email', 'token'));
        echo view('all.footer');
    }

    public function forgotPasswordEmailReset(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'new_password' => 'required|min:6|confirmed',
        ]);

        $email = mb_strtolower(trim((string) $request->email));
        $row = DB::table('password_reset_tokens')->where('email', $email)->first();
        if (! $row) {
            return back()->with('error', 'Token reset password tidak ditemukan.')->withInput();
        }
        if (! Hash::check((string) $request->token, (string) $row->token)) {
            return back()->with('error', 'Token reset password tidak valid.')->withInput();
        }
        if ($row->created_at && now()->diffInMinutes($row->created_at) > 60) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();

            return back()->with('error', 'Token reset password sudah kadaluarsa.');
        }

        $found = $this->findUserByEmail($email);
        if (! $found) {
            return back()->with('error', 'Email tidak terdaftar.');
        }

        DB::table('user')->where('userid', $found->userid)->update([
            'password' => Hash::make($request->new_password),
        ]);
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        return redirect('/login')->with('success', 'Password berhasil direset. Silakan login.');
    }

    public function forgotPasswordSendPhoneOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required',
        ]);

        $phone = $this->normalizePhoneNumber((string) $request->phone);
        $found = $this->findUserByPhone($phone);
        if (! $found) {
            return back()->with('error', 'Nomor telepon tidak terdaftar di database.')->withInput();
        }

        $otp = (string) random_int(100000, 999999);
        $key = 'pwd_reset_otp:'.$phone;
        Cache::put($key, [
            'userid' => $found->userid,
            'otp' => Hash::make($otp),
            'expires_at' => now()->addMinutes(10)->toDateTimeString(),
        ], now()->addMinutes(10));

        try {
            $response = Http::withHeaders([
                'Authorization' => env('FONNTE_TOKEN'),
            ])->post('https://api.fonnte.com/send', [
                'target' => $phone,
                'message' => "Kode OTP reset password Anda: *{$otp}*\nBerlaku 10 menit.",
                'countryCode' => '62',
            ]);

            if (! $response->successful()) {
                return back()->with('error', 'Gagal mengirim OTP WhatsApp.')->withInput();
            }
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan saat kirim OTP WhatsApp.')->withInput();
        }

        return redirect()
            ->route('password.forgot.phone.page')
            ->with('success', 'OTP reset password sudah dikirim via WhatsApp.')
            ->with('otp_phone', $phone)
            ->withInput(['phone' => $phone]);
    }

    public function forgotPasswordPhoneVerifyOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'otp' => 'required|digits:6',
        ]);

        $phone = $this->normalizePhoneNumber((string) $request->phone);
        $key = 'pwd_reset_otp:'.$phone;
        $payload = Cache::get($key);
        if (! is_array($payload)) {
            return redirect()->route('password.forgot.phone.page')
                ->with('error', 'OTP tidak ditemukan atau sudah kadaluarsa.')
                ->with('otp_phone', $phone)
                ->withInput(['phone' => $phone]);
        }

        $expiresAt = isset($payload['expires_at']) ? (string) $payload['expires_at'] : '';
        if ($expiresAt === '' || now()->gt(\Carbon\Carbon::parse($expiresAt))) {
            Cache::forget($key);

            return redirect()->route('password.forgot.phone.page')
                ->with('error', 'OTP sudah kadaluarsa.')
                ->with('otp_phone', $phone)
                ->withInput(['phone' => $phone]);
        }

        if (! Hash::check((string) $request->otp, (string) ($payload['otp'] ?? ''))) {
            return redirect()->route('password.forgot.phone.page')
                ->with('error', 'OTP salah.')
                ->with('otp_phone', $phone)
                ->withInput(['phone' => $phone]);
        }

        $userid = (int) ($payload['userid'] ?? 0);
        if ($userid <= 0) {
            return redirect()->route('password.forgot.phone.page')
                ->with('error', 'Data OTP tidak valid.')
                ->with('otp_phone', $phone)
                ->withInput(['phone' => $phone]);
        }

        Cache::forget($key);
        $resetToken = Str::random(64);
        Cache::put('pwd_reset_phone_token:'.$resetToken, [
            'userid' => $userid,
            'phone' => $phone,
        ], now()->addMinutes(10));

        return redirect()->route('password.forgot.phone.new.form', ['token' => $resetToken]);
    }

    public function forgotPasswordPhoneNewPasswordForm(Request $request)
    {
        $system = DB::table('system')->first();
        $token = (string) $request->query('token');
        $payload = Cache::get('pwd_reset_phone_token:'.$token);
        if ($token === '' || ! is_array($payload)) {
            return redirect()->route('password.forgot.phone.page')->with('error', 'Sesi reset password tidak valid atau kadaluarsa.');
        }

        echo view('all.header', compact('system'));
        echo view('all.reset_password_phone', compact('token'));
        echo view('all.footer');
    }

    public function forgotPasswordPhoneNewPassword(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'new_password' => 'required|min:6|confirmed',
        ]);

        $token = (string) $request->token;
        $payload = Cache::get('pwd_reset_phone_token:'.$token);
        if (! is_array($payload)) {
            return back()->with('error', 'Sesi reset password tidak valid atau kadaluarsa.');
        }

        $userid = (int) ($payload['userid'] ?? 0);
        if ($userid <= 0) {
            return back()->with('error', 'Data user tidak valid.');
        }

        DB::table('user')->where('userid', $userid)->update([
            'password' => Hash::make($request->new_password),
        ]);
        Cache::forget('pwd_reset_phone_token:'.$token);

        return redirect('/login')->with('success', 'Password berhasil direset. Silakan login.');
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

        if (! $user) {
            $card2_value = 0;
            $card2_label = 'Silakan login untuk melihat ringkasan';
        }

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
                    if (in_array((int) $teacher->roleid, [4, 5], true)) { // Kurikulum
                        // Kurikulum fokus ke jadwal, bukan tugas.
                        $activeYear = DB::table('academic_year')->where('is_active', 1)->first();
                        if ($activeYear) {
                            $card2_value = DB::table('schedule')
                                ->where('academic_year_id', $activeYear->academic_year_id)
                                ->count();
                            $card2_label = 'Total Jadwal Aktif';
                        } else {
                            $card2_value = 0;
                            $card2_label = 'Total Jadwal Aktif';
                        }

                        // Tidak tampilkan card tugas masuk untuk kurikulum.
                        $card3_value = 0;
                        $card3_label = '';

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

        ActivityLogger::log('Mengubah setting sistem', $request->ip());

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
