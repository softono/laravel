<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `type` stores the activity KEY (e.g. LOGIN_SUCCESS), not a display label.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_activities', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('user_id', 36);
            $table->string('device_id', 64)->nullable();
            $table->string('type', 50)->nullable();
            $table->text('data')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('client', 512)->nullable();
            $table->string('location')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent();

            $table->index('user_id', 'user_activity_user_idx');

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_activities');
    }
};
