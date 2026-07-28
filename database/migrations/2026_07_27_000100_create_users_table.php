<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Structural port of the Next app's `users` table
 * (src/server/models/user.ts, src/server/db/migrations/20260713131422_init.sql).
 *
 * Postgres `text` PK/FK columns become `char(36) ascii_bin` (UUID, generated
 * in PHP via HasUuids) because MySQL/MariaDB cannot index/PK a bare TEXT
 * column. See the plan's §1 for the full column-by-column justification.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->char('id', 36)->charset('ascii')->collation('ascii_bin')->primary();
            $table->string('email')->unique();
            $table->boolean('email_verified')->default(false);
            $table->string('image')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent();
            $table->boolean('two_factor_enabled')->default(false);
            $table->string('role', 20)->default('USER')->index();
            $table->text('permission')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('phone', 32)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('timezone', 64)->default('UTC');
            $table->string('registered_ip', 45)->charset('ascii')->collation('ascii_bin')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
