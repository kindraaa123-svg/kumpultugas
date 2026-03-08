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
        Schema::table('user', function (Blueprint $table) {
            $table->string('pending_email')->nullable();
            $table->string('email_verification_token')->nullable();
            $table->string('pending_phone')->nullable();
            $table->string('phone_otp')->nullable();
            $table->timestamp('phone_otp_expires_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user', function (Blueprint $table) {
            $table->dropColumn(['pending_email', 'email_verification_token', 'pending_phone', 'phone_otp', 'phone_otp_expires_at']);
        });
    }
};
