<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mirrors the Next.js Drizzle table `user_sessions` (same columns, order, nullability, defaults).
 * Postgres `text` is TEXT, except columns that are unique/indexed (VARCHAR, index-safe on MySQL).
 * Ids are `ascii_bin` so UUID/token comparisons stay case-sensitive.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_sessions', function (Blueprint $table) {
            $table->char('id', 36)->charset('ascii')->collation('ascii_bin')->default(new Expression('(UUID())'))->primary();
            $table->dateTime('expires_at');
            $table->string('token')->charset('ascii')->collation('ascii_bin')->unique('user_sessions_token_unique'); // unique, so VARCHAR
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at'); // NOT NULL, no default: the app sets it, as Next does
            $table->text('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->char('user_id', 36)->charset('ascii')->collation('ascii_bin');
            $table->text('device_uid')->nullable();
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
