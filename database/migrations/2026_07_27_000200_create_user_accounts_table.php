<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mirrors the Next.js Drizzle table `user_accounts` (same columns, order, nullability, defaults).
 * Postgres `text` is TEXT, except columns that are unique/indexed (VARCHAR, index-safe on MySQL).
 * Ids are `ascii_bin` so UUID/token comparisons stay case-sensitive.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_accounts', function (Blueprint $table) {
            $table->char('id', 36)->charset('ascii')->collation('ascii_bin')->default(new Expression('(UUID())'))->primary();
            $table->text('account_id');
            $table->text('provider_id');
            $table->char('user_id', 36)->charset('ascii')->collation('ascii_bin');
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->text('id_token')->nullable();
            $table->dateTime('access_token_expires_at')->nullable();
            $table->dateTime('refresh_token_expires_at')->nullable();
            $table->text('scope')->nullable();
            $table->text('password')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at'); // NOT NULL, no default: the app sets it, as Next does

            $table->index('user_id', 'user_account_userId_idx');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_accounts');
    }
};
