<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mirrors the Next.js Drizzle table `users` (same columns, order, nullability, defaults).
 * Postgres `text` is TEXT, except columns that are unique/indexed (VARCHAR, index-safe on MySQL).
 * Ids are `ascii_bin` so UUID/token comparisons stay case-sensitive.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->char('id', 36)->charset('ascii')->collation('ascii_bin')->default(new Expression('(UUID())'))->primary();
            $table->string('email', 320)->unique('users_email_unique');
            $table->boolean('email_verified')->default(false);
            $table->text('image')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->boolean('two_factor_enabled')->nullable()->default(false);
            $table->text('role')->nullable()->default(new Expression("('USER')"));
            $table->text('permission')->nullable();
            $table->enum('status', ['active', 'inactive'])->nullable()->default('active');
            $table->text('first_name');
            $table->text('last_name');
            $table->text('phone')->nullable();
            $table->text('country')->nullable();
            $table->text('timezone')->nullable()->default(new Expression("('UTC')"));
            $table->text('registered_ip')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
