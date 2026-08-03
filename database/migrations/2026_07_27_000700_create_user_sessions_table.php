<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `token` collation MUST be binary - session tokens are base64url and case
 * significant; a case-insensitive collation would collapse the keyspace.
 * `updated_at` is NOT NULL with no default - Eloquent always supplies it;
 * raw inserts must too.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_sessions', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->dateTime('expires_at');
            $table->string('token', 64)->unique();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at'); // NOT NULL, no default - Next parity
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->char('user_id', 36);
            $table->string('device_uid', 64)->nullable();
            $table->boolean('remember')->default(false);

            $table->index('user_id', 'user_sessions_userId_idx');

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_sessions');
    }
};
