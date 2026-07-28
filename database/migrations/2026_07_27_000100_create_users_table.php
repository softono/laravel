<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `id` is `char(36) ascii_bin` (UUID, generated in PHP via HasUuids)
 * rather than an auto-increment int, so it can be shared as a stable
 * foreign key across every auth table without a join to a separate
 * identity table.
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
