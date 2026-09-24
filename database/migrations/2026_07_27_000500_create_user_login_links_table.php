<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mirrors the Next.js Drizzle table `user_login_links` (same columns, order, nullability, defaults).
 * Postgres `text` is TEXT, except columns that are unique/indexed (VARCHAR, index-safe on MySQL).
 * Ids are `ascii_bin` so UUID/token comparisons stay case-sensitive.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_login_links', function (Blueprint $table) {
            $table->char('id', 36)->charset('ascii')->collation('ascii_bin')->default(new Expression('(UUID())'))->primary();
            $table->text('purpose')->charset('ascii')->collation('ascii_bin');
            $table->text('email');
            $table->char('user_id', 36)->charset('ascii')->collation('ascii_bin');
            $table->text('poll_token_hash')->charset('ascii')->collation('ascii_bin');
            $table->text('link_token_hash')->charset('ascii')->collation('ascii_bin');
            $table->text('code')->charset('ascii')->collation('ascii_bin');
            $table->string('status')->charset('ascii')->collation('ascii_bin')->default('pending'); // indexed, so VARCHAR
            $table->text('device_name')->nullable();
            $table->text('location')->nullable();
            $table->text('ip')->nullable();
            $table->boolean('remember')->default(false);
            $table->boolean('trust_device')->default(false);
            $table->text('tfa_handle')->charset('ascii')->collation('ascii_bin')->nullable();
            $table->dateTime('expires_at');
            $table->dateTime('approved_at')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index('user_id', 'user_login_links_user_idx');
            $table->index('status', 'user_login_links_status_idx');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_login_links');
    }
};
