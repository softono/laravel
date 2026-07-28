<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * NOTE: the stock `users` and `password_reset_tokens` tables have been
     * removed from this migration. Auth now lives in `users` created by
     * 2026_07_27_000100_create_users_table.php (Next-parity schema) and
     * password resets use `user_verifications`. The `sessions` table is
     * kept (guarded, since it already exists in the live DB and this app
     * has never had a `migrations` table) because SESSION_DRIVER can still
     * fall back to it.
     */
    public function up(): void
    {
        if (! Schema::hasTable('sessions')) {
            Schema::create('sessions', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->foreignId('user_id')->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};
