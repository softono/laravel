<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Port of src/server/models/user-account.ts.
 * `updated_at` is intentionally NOT NULL with no default, mirroring the
 * Next DDL (Eloquent always supplies it; raw inserts must too).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_accounts', function (Blueprint $table) {
            $table->char('id', 36)->charset('ascii')->collation('ascii_bin')->primary();
            $table->string('account_id')->charset('ascii')->collation('ascii_bin');
            $table->string('provider_id', 50)->charset('ascii')->collation('ascii_bin');
            $table->char('user_id', 36)->charset('ascii')->collation('ascii_bin');
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->text('id_token')->nullable();
            $table->dateTime('access_token_expires_at')->nullable();
            $table->dateTime('refresh_token_expires_at')->nullable();
            $table->string('scope')->nullable();
            $table->string('password')->charset('ascii')->collation('ascii_bin')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at'); // NOT NULL, no default - Next parity

            $table->index('user_id', 'user_account_userId_idx');
            // Intentional addition beyond Next: needed for findAccountByProvider lookups.
            $table->index(['provider_id', 'account_id'], 'user_accounts_provider_account_idx');

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_accounts');
    }
};
