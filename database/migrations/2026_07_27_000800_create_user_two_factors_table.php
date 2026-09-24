<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mirrors the Next.js Drizzle table `user_two_factors` (same columns, order, nullability, defaults).
 * Postgres `text` is TEXT, except columns that are unique/indexed (VARCHAR, index-safe on MySQL).
 * Ids are `ascii_bin` so UUID/token comparisons stay case-sensitive.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_two_factors', function (Blueprint $table) {
            $table->char('id', 36)->charset('ascii')->collation('ascii_bin')->default(new Expression('(UUID())'))->primary();
            $table->text('secret');
            $table->text('backup_codes');
            $table->char('user_id', 36)->charset('ascii')->collation('ascii_bin');
            $table->boolean('verified')->nullable()->default(false);

            $table->index('user_id', 'user_two_factors_userId_idx');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_two_factors');
    }
};
