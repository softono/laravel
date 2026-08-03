<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * No timestamps at all - this table only ever holds current 2FA state.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_two_factors', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->text('secret');
            $table->text('backup_codes');
            $table->char('user_id', 36);
            $table->boolean('verified')->default(false);

            $table->index('user_id', 'user_two_factors_userId_idx');

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_two_factors');
    }
};
