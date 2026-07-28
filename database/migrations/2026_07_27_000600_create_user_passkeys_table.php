<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * WebAuthn credentials. `created_at` is nullable with no default and
 * there is no `updated_at` - see the UserPasskey model ($timestamps = false).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_passkeys', function (Blueprint $table) {
            $table->char('id', 36)->charset('ascii')->collation('ascii_bin')->primary();
            $table->string('name', 100)->nullable();
            $table->text('public_key');
            $table->char('user_id', 36)->charset('ascii')->collation('ascii_bin');
            $table->string('credential_id')->charset('ascii')->collation('ascii_bin');
            $table->integer('counter');
            $table->string('device_type', 32);
            $table->boolean('backed_up');
            $table->string('transports')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->char('aaguid', 36)->charset('ascii')->collation('ascii_bin')->nullable();

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
