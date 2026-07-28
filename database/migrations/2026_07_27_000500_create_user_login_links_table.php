<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Magic login link / second-device approval flow.
 * `purpose`: 'signin' | 'tfa'. `status`: pending|approved|consumed|rejected|expired.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_login_links', function (Blueprint $table) {
            $table->char('id', 36)->charset('ascii')->collation('ascii_bin')->primary();
            $table->string('purpose', 20)->charset('ascii')->collation('ascii_bin');
            $table->string('email');
            $table->char('user_id', 36)->charset('ascii')->collation('ascii_bin');
            $table->string('poll_token_hash')->charset('ascii')->collation('ascii_bin');
            $table->string('link_token_hash')->charset('ascii')->collation('ascii_bin');
            $table->char('code', 6)->charset('ascii')->collation('ascii_bin');
            $table->string('status', 20)->charset('ascii')->collation('ascii_bin')->default('pending');
            $table->string('device_name')->nullable();
            $table->string('location')->nullable();
            $table->string('ip', 45)->charset('ascii')->collation('ascii_bin')->nullable();
            $table->boolean('remember')->default(false);
            $table->boolean('trust_device')->default(false);
            $table->string('tfa_handle', 64)->charset('ascii')->collation('ascii_bin')->nullable();
            $table->dateTime('expires_at');
            $table->dateTime('approved_at')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent();

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
