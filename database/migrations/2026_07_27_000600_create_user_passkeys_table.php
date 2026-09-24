<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mirrors the Next.js Drizzle table `user_passkeys` (same columns, order, nullability, defaults).
 * Postgres `text` is TEXT, except columns that are unique/indexed (VARCHAR, index-safe on MySQL).
 * Ids are `ascii_bin` so UUID/token comparisons stay case-sensitive.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_passkeys', function (Blueprint $table) {
            $table->char('id', 36)->charset('ascii')->collation('ascii_bin')->default(new Expression('(UUID())'))->primary();
            $table->text('name')->nullable();
            $table->text('public_key');
            $table->char('user_id', 36)->charset('ascii')->collation('ascii_bin');
            $table->string('credential_id')->charset('ascii')->collation('ascii_bin'); // indexed, so VARCHAR
            $table->integer('counter');
            $table->text('device_type');
            $table->boolean('backed_up');
            $table->text('transports')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->text('aaguid')->nullable();

            $table->index('user_id', 'user_passkey_userId_idx');
            $table->index('credential_id', 'user_passkey_credentialID_idx');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_passkeys');
    }
};
