<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('trash_logs')) {
            return;
        }

        Schema::create('trash_logs', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type', 50);
            $table->unsignedInteger('entity_id');
            $table->string('action', 20);
            $table->json('before_json')->nullable();
            $table->json('after_json')->nullable();
            $table->unsignedInteger('performed_userid')->nullable();
            $table->string('performed_username', 255)->nullable();
            $table->timestamps();

            $table->index(['entity_type', 'entity_id']);
            $table->index(['action', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trash_logs');
    }
};
