<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('academic_year', function (Blueprint $table) {
            $table->increments('academic_year_id');
            $table->string('name', 9);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->tinyInteger('is_active')->default(0);
            $table->timestamps();
        });

        Schema::create('block', function (Blueprint $table) {
            $table->increments('block_id');
            $table->unsignedInteger('academic_year_id');
            $table->string('name', 50);
            $table->unsignedTinyInteger('order_no');
            $table->unsignedTinyInteger('day_of_week')->nullable();
            $table->date('date_start')->nullable();
            $table->date('date_end')->nullable();
            $table->timestamps();
        });

        Schema::create('class', function (Blueprint $table) {
            $table->increments('classid');
            $table->string('classname', 255);
        });

        Schema::create('course', function (Blueprint $table) {
            $table->increments('courseid');
            $table->string('coursename', 255);
        });

        Schema::create('employer', function (Blueprint $table) {
            $table->increments('employerid');
            $table->string('name', 255);
            $table->string('email', 255);
            $table->string('phonenumber', 255);
            $table->integer('roleid');
            $table->integer('userid');
        });

        Schema::create('jadwal', function (Blueprint $table) {
            $table->increments('jadwal_id');
            $table->unsignedInteger('academic_year_id');
            $table->unsignedInteger('block_id');
            $table->integer('classid');
        });

        Schema::create('level', function (Blueprint $table) {
            $table->increments('levelid');
            $table->string('levelname', 255);
        });

        Schema::create('role', function (Blueprint $table) {
            $table->increments('roleid');
            $table->string('rolename', 255);
        });

        Schema::create('schedule', function (Blueprint $table) {
            $table->increments('scheduleid');
            $table->integer('academic_year_id');
            $table->integer('courseid');
            $table->integer('classid');
            $table->integer('teacherid');
            $table->timestamps();
        });

        Schema::create('student', function (Blueprint $table) {
            $table->increments('studentid');
            $table->string('name', 255);
            $table->string('email', 255);
            $table->string('phonenumber', 255);
            $table->integer('classid');
            $table->integer('userid');
        });

        Schema::create('teacher', function (Blueprint $table) {
            $table->increments('teacherid');
            $table->string('name', 255);
            $table->string('email', 255);
            $table->string('phonenumber', 255);
            $table->integer('roleid');
            $table->integer('userid');
        });

        Schema::create('user', function (Blueprint $table) {
            $table->increments('userid');
            $table->string('username', 255);
            $table->string('password', 255);
            $table->integer('levelid');
            $table->dateTime('verified_at')->nullable();
            $table->string('activation_token', 255)->nullable();
            $table->dateTime('activation_token_expired')->nullable();
            $table->string('reset_password_token', 255)->nullable();
            $table->dateTime('reset_password_expired')->nullable();
        });

        Schema::create('assignment', function (Blueprint $table) {
            $table->increments('assignmentid');
            $table->integer('scheduleid');
            $table->string('name', 255);
            $table->string('description', 255)->nullable();
            $table->dateTime('time_start');
            $table->dateTime('time_end');
            $table->dateTime('created_at');
        });

        Schema::create('quest', function (Blueprint $table) {
            $table->increments('questid');
            $table->integer('assignmentid');
            $table->string('file', 255);
            $table->string('description', 255)->nullable();
            $table->integer('studentid');
            $table->integer('score')->nullable();
            $table->string('feedback', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quest');
        Schema::dropIfExists('assignment');
        Schema::dropIfExists('user');
        Schema::dropIfExists('teacher');
        Schema::dropIfExists('student');
        Schema::dropIfExists('schedule');
        Schema::dropIfExists('role');
        Schema::dropIfExists('level');
        Schema::dropIfExists('jadwal');
        Schema::dropIfExists('employer');
        Schema::dropIfExists('course');
        Schema::dropIfExists('class');
        Schema::dropIfExists('block');
        Schema::dropIfExists('academic_year');
    }
};
