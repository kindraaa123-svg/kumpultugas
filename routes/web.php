<?php

use Illuminate\Support\Facades\Route;

Route::fallback('App\Http\Controllers\Ctrl@notfound');

Route::get('/login', 'App\Http\Controllers\Ctrl@login');
Route::post('/login/process', 'App\Http\Controllers\Ctrl@loginact');
Route::get('/logout', 'App\Http\Controllers\Ctrl@logout');


Route::get('/home', 'App\Http\Controllers\Ctrl@home');

Route::get('/course', 'App\Http\Controllers\Ctrl@course');
Route::get('/profile', 'App\Http\Controllers\Ctrl@profile')->name('profile');
Route::post('/profile/update', 'App\Http\Controllers\Ctrl@updateprofile')->name('profile.update');
Route::post('/profile/password', 'App\Http\Controllers\Ctrl@updatepassword')->name('profile.password');

Route::get('/userdata', 'App\Http\Controllers\Ctrl@userdata');
Route::post('/userdata/add', 'App\Http\Controllers\Ctrl@saveuser')->name('userdata.add');
Route::get('/userdata/reset/{id}', 'App\Http\Controllers\Ctrl@userresetpassword')->name('userdata.reset');
Route::get('/userdata/delete/{id}', 'App\Http\Controllers\Ctrl@deleteuser')->name('userdata.delete');

Route::get('/setting', 'App\Http\Controllers\Ctrl@setting');
Route::post('/setting/update', 'App\Http\Controllers\Ctrl@savesetting');

Route::get('/database', 'App\Http\Controllers\Ctrl@databasePage');
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
Route::get('/assignment/create', 'App\Http\Controllers\AssignmentController@create')->name('assignment.create');
Route::post('/assignment/store', 'App\Http\Controllers\AssignmentController@store')->name('assignment.store');
Route::get('/assignment/show/{id}', 'App\Http\Controllers\AssignmentController@show')->name('assignment.show');
Route::post('/assignment/upload', 'App\Http\Controllers\AssignmentController@upload')->name('assignment.upload');
Route::post('/assignment/update', 'App\Http\Controllers\AssignmentController@update')->name('assignment.update');
Route::get('/assignment/delete/{id}', 'App\Http\Controllers\AssignmentController@delete')->name('assignment.delete');
Route::get('/assignment/review', 'App\Http\Controllers\AssignmentController@review')->name('assignment.review');
Route::post('/assignment/grade', 'App\Http\Controllers\AssignmentController@grade')->name('assignment.grade');
