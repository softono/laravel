<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Port of src/server/models/user-two-factor.ts.
 * No timestamps at all, mirroring Next exactly.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_two_factors', function (Blueprint $table) {
            $table->char('id', 36)->charset('ascii')->collation('ascii_bin')->primary();
            $table->text('secret');
            $table->text('backup_codes');
            $table->char('user_id', 36)->charset('ascii')->collation('ascii_bin');
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
