<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * OTP store for email verification / password reset / signin / 2FA email
 * codes. `identifier` = "{purpose}:{lowercased email}". No FK to users -
 * lookups go purely by identifier.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_verifications', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('identifier', 320);
            $table->string('value');
            $table->dateTime('expires_at');
            $table->unsignedInteger('attempts')->default(0);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent();

            $table->index('identifier', 'user_verification_identifier_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_verifications');
    }
};
