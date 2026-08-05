<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Drops the public-site tables cut per docs/local/prd.md ("Do NOT create
 * a frontend website... Remove all frontend/public-site functionality").
 * A separate migration rather than editing the historical create-table
 * migrations in place, since those already ran against real databases.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('blogs');
        Schema::dropIfExists('contact_messages');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('seos');
    }

    public function down(): void
    {
        // Frontend/public-site tables are gone for good - nothing to restore.
    }
};
