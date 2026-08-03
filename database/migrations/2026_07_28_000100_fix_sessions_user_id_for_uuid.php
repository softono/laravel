<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Laravel's stock `sessions` table declares `user_id` as BIGINT UNSIGNED,
 * which predates this app's move to UUID (char(36)) user ids. With
 * SESSION_DRIVER=database, StartSession writes the authenticated user's id
 * into that column on every request - MySQL then truncates the UUID and
 * raises "Data truncated for column 'user_id'", 500-ing every authenticated
 * page. Widen the column to match users.id byte-for-byte.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sessions')) {
            return;
        }

        // Any rows written before this fix hold truncated/garbage ids.
        DB::table('sessions')->update(['user_id' => null]);

        DB::statement('ALTER TABLE sessions ALTER COLUMN user_id TYPE VARCHAR(36)');
    }

    public function down(): void
    {
        if (! Schema::hasTable('sessions')) {
            return;
        }

        DB::table('sessions')->update(['user_id' => null]);

        DB::statement('ALTER TABLE sessions ALTER COLUMN user_id TYPE BIGINT USING (user_id::bigint)');
    }
};
