<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('storage_api_keys', function (Blueprint $table) {
            $table->id();
            $table->char('user_id', 36)->charset('ascii')->collation('ascii_bin');
            // Looked up on every API request (HMAC auth resolves the
            // secret by access_key) - ascii_bin keeps the keyspace
            // case-sensitive, matching every other token column in this app.
            $table->string('access_key', 40)->charset('ascii')->collation('ascii_bin')->unique();
            // Encrypted at rest (Crypt::encryptString, backed by APP_KEY) -
            // not one-way hashed like a password, because HMAC request
            // verification needs the plaintext secret back server-side.
            // Never logged or displayed in plaintext after issuance.
            $table->text('secret_key');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->dateTime('last_used_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('storage_api_keys');
    }
};
