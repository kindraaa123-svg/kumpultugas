<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;


class Ctrl extends Controller
{

    public function notfound(){
        return response()->view('all.error', [], 404);
    }

//==========================================================================================

    public function login(){
        $system = DB::table('system')->first();
        echo view ('all.header',compact('system'));
        echo view ('all.login',compact('system'));
        echo view ('all.footer');
    }

    public function loginact(Request $request){
        $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        $user = DB::table('user')
            ->where('username', $request->username)
            ->first();

        if (!$user) {
            return back()->with('error', 'Username tidak ada');
        }

        if (!Hash::check($request->password, $user->password)) {
            return back()->with('error', 'Salah Password');
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
            'is_login' => true
        ]);

        return redirect('/home')->with('success', 'Login berhasil');
    }

    public function logout(Request $request){
        $userid = $request->session()->get('userid');


        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect ('/home');
    }

//==============================================================================================
    public function forgetemail(){
        echo view ('all.header',compact('system'));
        echo view ('all.forgetpasswordemail');
        echo view ('all.footer');
    }

//==============================================================================================

    public function home(){
        $system = DB::table('system')->first();
        echo view ('all.header',compact('system'));
        echo view ('all.menu', compact('system'));
        echo view ('all.home',compact('system'));
        echo view ('all.footer');
    }

//===========================================================================================

    public function course(){
        $system=DB::table('system')->first();
        echo view ('all.header',compact('system'));
        echo view('all.menu',compact('system'));
        echo view('all.allcourse');
        echo view('all.footer');
    }

    public function profile(){
        $system = DB::table('system')->first();
        $user = DB::table('user')->where('userid', session('userid'))->first();
        $data = null;
        if ($user) {
            if ($user->levelid == 3) {
                $data = DB::table('student')->where('userid', $user->userid)->first();
            } elseif ($user->levelid == 1) {
                $data = DB::table('employer')->where('userid', $user->userid)->first();
            } else {
                $data = DB::table('teacher')->where('userid', $user->userid)->first();
            }
        }
        echo view ('all.header',compact('system'));
        echo view ('all.menu', compact('system'));
        echo view ('all.profile',compact('user','data'));
        echo view ('all.footer');
    }

    public function updateprofile(Request $request){
        $user = DB::table('user')->where('userid', session('userid'))->first();
        if (!$user) return back()->with('error', 'User tidak ditemukan');

        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'phonenumber' => 'required',
        ]);

        if ($user->levelid == 3) {
            DB::table('student')->where('userid', $user->userid)->update([
                'name' => $request->name,
                'email' => $request->email,
                'phonenumber' => $request->phonenumber,
            ]);
        } elseif ($user->levelid == 1) {
            DB::table('employer')->where('userid', $user->userid)->update([
                'name' => $request->name,
                'email' => $request->email,
                'phonenumber' => $request->phonenumber,
            ]);
        } else {
            DB::table('teacher')->where('userid', $user->userid)->update([
                'name' => $request->name,
                'email' => $request->email,
                'phonenumber' => $request->phonenumber,
            ]);
        }

        Session::put([
            'name' => $request->name,
            'email' => $request->email,
            'phonenumber' => $request->phonenumber,
        ]);

        return back()->with('success', 'Profil berhasil diperbarui');
    }
    
    public function updatepassword(Request $request){
        $user = DB::table('user')->where('userid', session('userid'))->first();
        if (!$user) return back()->with('error', 'User tidak ditemukan');
        
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|min:6',
        ]);
        
        if (!Hash::check($request->old_password, $user->password)) {
            return back()->with('error', 'Password lama salah');
        }
        
        DB::table('user')->where('userid', $user->userid)->update([
            'password' => Hash::make($request->new_password),
        ]);
        
        return back()->with('success', 'Password berhasil diperbarui');
    }
//=====================================================================================

    public function userdata(){
        $data=DB::table('user')
            ->leftjoin('level','level.levelid','=','user.levelid')
            ->leftjoin('employer','employer.userid','=','user.userid')
            ->leftjoin('teacher','teacher.userid','=','user.userid')
            ->leftjoin('student','student.userid','=','user.userid')
            ->leftJoin('role', function($join) {
                $join->on('role.roleid', '=', 'employer.roleid')
                ->orOn('role.roleid', '=', 'teacher.roleid');
            })
            ->select(
                'user.userid',
                'user.username',
                'level.levelname',
                'role.rolename',

                DB::raw('COALESCE(teacher.email, employer.email,student.email) as email'),
                DB::raw('COALESCE(teacher.phonenumber, employer.phonenumber,student.phonenumber) as phonenumber'),
                DB::raw('COALESCE(teacher.name, employer.name,student.name) as name'),
            )
            ->get();
        $system = DB::table('system')->first();
        $level = DB::table('level')->get();
        $role = DB::table('role')->get();
        echo view ('all.header',compact('system'));
        echo view ('all.menu', compact('system'));
        echo view ('admin.userdata',compact('data','level','role'));
        echo view ('all.footer');
    }

    public function saveuser(Request $request){
        $rules = [
            'name' => 'required',
            'username' => 'required|unique:user,username',
            'email' => 'required|unique:student,email|unique:employer,email|unique:teacher,email',
                'phonenumber' => 'required|unique:student,phonenumber|unique:employer,phonenumber|unique:teacher,phonenumber',
                'level' => 'required',
        ];
        if ($request->level == 3) {
            $rules['role'] = 'nullable';
        } else {
            $rules['role'] = 'required';
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
                'userid' => $userid,
            ]);
        } else if ($request->level == 1){
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
                ->with('error', 'Failed to add user: ' . $e->getMessage());
        }
    }

    public function deleteuser($id){
        $user = DB::table('user')
               ->where('userid',$id)
               ->first();

        $student = DB::table('student')
               ->where('userid',$id)
               ->first();

        $employer = DB::table('employer')
               ->where('userid',$id)
               ->first();

        $teacher = DB::table('teacher')
               ->where('userid',$id)
               ->first();

        DB::table('user')
               ->where('userid',$id)
               ->delete();

        DB::table('student')
               ->where('userid',$id)
               ->delete();

        DB::table('employer')
               ->where('userid',$id)
               ->delete();

        DB::table('teacher')
               ->where('userid',$id)
               ->delete();

        return back();
    }

    public function userresetpassword($id){
        $user = DB::table('user')->where('userid', $id)->first();

        DB::table('user')->where('userid', $id)->update([
            'password' => Hash::make('12345')
        ]);

        return back();
    }

//====================================================================================


//====================================================================================
    public function setting(){
        $system = DB::table('system')->first();
        echo view ('all.header',compact('system'));
        echo view ('all.menu', compact('system'));
        echo view ('superadmin.setting',compact('system'));
        echo view ('all.footer');
    }

    public function savesetting(Request $request){
        $old = DB::table('system')
            ->where('systemid', $request->systemid)
            ->first();

        $request->validate([
            'name'=>'required',
            'logo'=>'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'address'=>'required',
            'manager'=>'required',
            'contact'=>'required',
        ]);

        if ($request->hasFile('logo')) {
            if ($old && $old->systemlogo) {
                Storage::delete('public/' . $old->systemlogo);
            }
            $fotoPath = $request->file('logo')->store('uploads', 'public');
        } else {
            $fotoPath = $old->systemlogo; // pakai logo lama
        }

        $data = [
            'systemname'     => $request->name,
            'systemlogo'     => $fotoPath,
            'systemaddress'  => $request->address,
            'systemmanager'        => $request->manager,
            'systemcontact'  => $request->contact,
        ];

        DB::table('system')
            ->where('systemid', $request->systemid)
            ->update($data);
        return redirect('/setting')->with('success','Setting succesafully updated');
    }

//========================================================================================================
    public function databasePage(){
        $system = DB::table('system')->first();

        echo view ('all.header',compact('system'));
        echo view('all.menu', compact('system'));
        echo view('superadmin.database');
        echo view('all.footer');
    }

    public function exportDatabase(){
        $tables = array_map('current', DB::select('SHOW TABLES'));

        $sql = "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            $create = DB::select("SHOW CREATE TABLE `$table`")[0]->{"Create Table"};
            $sql .= "DROP TABLE IF EXISTS `$table`;\n";
            $sql .= $create . ";\n\n";

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
                    return "'" . str_replace("'", "''", $value) . "'";
                }, array_values((array) $row));

                $sql .= "REPLACE INTO `$table` (`" . implode('`,`', $columns) . "`) VALUES (" . implode(',', $values) . ");\n";
            }

            $sql .= "\n";
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        $filename = 'backup_' . date('Ymd_His') . '.sql';

        return response($sql)
            ->header('Content-Type', 'application/sql')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
    }

    public function importDatabase(Request $request){
        $request->validate([
            'backup_file' => 'required|file'
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
                DB::unprepared($statement . ';');
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Database imported successfully');
    }    

}
