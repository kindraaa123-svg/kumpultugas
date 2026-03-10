<?php

use Illuminate\Support\Facades\Route;

Route::fallback('App\Http\Controllers\Ctrl@notfound');

Route::get('/', 'App\Http\Controllers\Ctrl@home');

Route::get('/login', 'App\Http\Controllers\Ctrl@login');
Route::post('/login/process', 'App\Http\Controllers\Ctrl@loginact');
Route::get('/logout', 'App\Http\Controllers\Ctrl@logout');

Route::get('/home', 'App\Http\Controllers\Ctrl@home');

Route::get('/profile', 'App\Http\Controllers\Ctrl@profile')->name('profile');
Route::post('/profile/update', 'App\Http\Controllers\Ctrl@updateprofile')->name('profile.update');
Route::post('/profile/password', 'App\Http\Controllers\Ctrl@updatepassword')->name('profile.password');

Route::post('/profile/email', 'App\Http\Controllers\Ctrl@requestEmailChange')->name('profile.email');
Route::get('/profile/email/verify/{token}', 'App\Http\Controllers\Ctrl@verifyEmailChange')->name('profile.email.verify');

Route::post('/profile/phone', 'App\Http\Controllers\Ctrl@requestPhoneChange')->name('profile.phone');
Route::get('/profile/phone/cancel', 'App\Http\Controllers\Ctrl@cancelPhoneChange')->name('profile.phone.cancel');
Route::post('/profile/phone/verify', 'App\Http\Controllers\Ctrl@verifyPhoneChange')->name('profile.phone.verify');

Route::get('/course', 'App\Http\Controllers\Ctrl@allcourse')->name('course.index');
Route::post('/course/store', 'App\Http\Controllers\Ctrl@savecourse')->name('course.store');
Route::post('/course/update', 'App\Http\Controllers\Ctrl@updatecourse')->name('course.update');
Route::get('/course/delete/{id}', 'App\Http\Controllers\Ctrl@deletecourse')->name('course.delete');

Route::get('/class', 'App\Http\Controllers\Ctrl@allclass')->name('class.index');
Route::post('/class/store', 'App\Http\Controllers\Ctrl@saveclass')->name('class.store');
Route::post('/class/update', 'App\Http\Controllers\Ctrl@updateclass')->name('class.update');
Route::get('/class/delete/{id}', 'App\Http\Controllers\Ctrl@deleteclass')->name('class.delete');

Route::get('/block', 'App\Http\Controllers\Ctrl@allblock')->name('block.index');
Route::post('/block/store', 'App\Http\Controllers\Ctrl@saveblock')->name('block.store');
Route::post('/block/update', 'App\Http\Controllers\Ctrl@updateblock')->name('block.update');
Route::get('/block/delete/{id}', 'App\Http\Controllers\Ctrl@deleteblock')->name('block.delete');

Route::get('/academicyear', 'App\Http\Controllers\Ctrl@allacademicyear')->name('academicyear.index');
Route::post('/academicyear/store', 'App\Http\Controllers\Ctrl@saveacademicyear')->name('academicyear.store');
Route::post('/academicyear/update', 'App\Http\Controllers\Ctrl@updateacademicyear')->name('academicyear.update');
Route::get('/academicyear/delete/{id}', 'App\Http\Controllers\Ctrl@deleteacademicyear')->name('academicyear.delete');

Route::get('/userdata', 'App\Http\Controllers\Ctrl@userdata');
Route::post('/userdata/add', 'App\Http\Controllers\Ctrl@saveuser')->name('userdata.add');
Route::get('/userdata/reset/{id}', 'App\Http\Controllers\Ctrl@userresetpassword')->name('userdata.reset');
Route::get('/userdata/delete/{id}', 'App\Http\Controllers\Ctrl@deleteuser')->name('userdata.delete');

Route::get('/setting', 'App\Http\Controllers\Ctrl@setting');
Route::post('/setting/update', 'App\Http\Controllers\Ctrl@savesetting');

Route::get('/database', 'App\Http\Controllers\Ctrl@databasePage');
Route::get('/activity-log', 'App\Http\Controllers\Ctrl@activityLog')->name('activity.log');
Route::get('/hakakses', 'App\Http\Controllers\Ctrl@hakaksesPage')->name('hakakses.index');
Route::post('/hakakses/update', 'App\Http\Controllers\Ctrl@hakaksesUpdate')->name('hakakses.update');
Route::get('/trash', 'App\Http\Controllers\Ctrl@trash')->name('trash.index');
Route::post('/trash/restore', 'App\Http\Controllers\Ctrl@trashRestore')->name('trash.restore');
Route::post('/trash/delete', 'App\Http\Controllers\Ctrl@trashDelete')->name('trash.delete');
Route::get('/database/export', 'App\Http\Controllers\Ctrl@exportDatabase');
Route::post('/database/import', 'App\Http\Controllers\Ctrl@importDatabase');

// Jadwal
Route::get('/jadwal', 'App\Http\Controllers\JadwalController@index')->name('jadwal.index');
Route::get('/jadwal/setting', 'App\Http\Controllers\JadwalController@setting')->name('jadwal.setting');
Route::post('/jadwal/setting/update', 'App\Http\Controllers\JadwalController@updateSetting')->name('jadwal.setting.update');
Route::get('/jadwal/edit/{classid}/{session}', 'App\Http\Controllers\JadwalController@editSchedule')->name('jadwal.edit');
Route::post('/jadwal/update', 'App\Http\Controllers\JadwalController@updateSchedule')->name('jadwal.update');

// Assignment (Tugas)
Route::get('/assignment', 'App\Http\Controllers\AssignmentController@index')->name('assignment.index');
Route::get('/assignment/filter', 'App\Http\Controllers\AssignmentController@filter')->name('assignment.filter');
Route::get('/assignment/courses/filter', 'App\Http\Controllers\AssignmentController@filterCourses')->name('assignment.courses.filter');
Route::get('/assignment/schedules/filter', 'App\Http\Controllers\AssignmentController@filterTeacherSchedules')->name('assignment.schedules.filter');
Route::post('/assignment/room/store', 'App\Http\Controllers\AssignmentController@storeRoom')->name('assignment.room.store');
Route::get('/assignment/create', 'App\Http\Controllers\AssignmentController@create')->name('assignment.create');
Route::post('/assignment/store', 'App\Http\Controllers\AssignmentController@store')->name('assignment.store');
Route::post('/assignment/delete', 'App\Http\Controllers\AssignmentController@deleteAjax')->name('assignment.delete.ajax');
Route::get('/assignment/show/{id}', 'App\Http\Controllers\AssignmentController@show')->name('assignment.show');
Route::post('/assignment/upload', 'App\Http\Controllers\AssignmentController@upload')->name('assignment.upload');
Route::post('/assignment/update', 'App\Http\Controllers\AssignmentController@update')->name('assignment.update');
Route::get('/assignment/delete/{id}', 'App\Http\Controllers\AssignmentController@delete')->name('assignment.delete');
Route::post('/assignment/room/delete', 'App\Http\Controllers\AssignmentController@deleteRoom')->name('assignment.room.delete');
Route::get('/assignment/review', 'App\Http\Controllers\AssignmentController@review')->name('assignment.review');
Route::get('/assignment/review/filter', 'App\Http\Controllers\AssignmentController@reviewFilter')->name('assignment.review.filter');
Route::post('/assignment/grade', 'App\Http\Controllers\AssignmentController@grade')->name('assignment.grade');
