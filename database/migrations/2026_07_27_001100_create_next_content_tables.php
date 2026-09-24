<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Content tables mirrored 1:1 from the Next.js app's Drizzle schema
 * (same table names, column names, nullability, defaults and indexes).
 * Postgres `serial` is an auto-increment INT, `timestamptz DEFAULT now()` is
 * `DATETIME DEFAULT CURRENT_TIMESTAMP`, and `text` is TEXT except unique columns (VARCHAR).
 * `updated_at` also gets ON UPDATE CURRENT_TIMESTAMP because the Laravel models don't set it.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Next has no updated_at on this table.
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->integer('id', true); // Postgres serial
            $table->char('user_id', 36)->charset('ascii')->collation('ascii_bin')->nullable();
            $table->text('to_user');
            $table->text('subject');
            $table->text('message');
            $table->dateTime('created_at')->useCurrent();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('email_templates', function (Blueprint $table) {
            $table->integer('id', true); // Postgres serial
            $table->string('key')->unique('email_templates_key_unique');
            $table->text('title')->nullable();
            $table->text('subject');
            $table->longText('body'); // Next `text` is unbounded; HTML can exceed TEXT's 64KB
            $table->text('params')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('notes', function (Blueprint $table) {
            $table->integer('id', true); // Postgres serial
            $table->char('user_id', 36)->charset('ascii')->collation('ascii_bin');
            $table->text('title');
            $table->text('note')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index('user_id', 'notes_user_idx');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('pages', function (Blueprint $table) {
            $table->integer('id', true); // Postgres serial
            $table->string('slug')->unique('pages_slug_unique');
            $table->text('title');
            $table->longText('body');
            $table->text('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('inactive');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('seos', function (Blueprint $table) {
            $table->integer('id', true); // Postgres serial
            $table->text('type')->default(new Expression("('STATIC')"));
            $table->string('url')->unique('seos_url_unique');
            $table->text('title')->nullable();
            $table->text('meta_title')->nullable();
            $table->text('keyword')->nullable();
            $table->text('meta_keyword')->nullable();
            $table->text('description')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('image')->nullable();
            $table->text('canonical')->nullable();
            $table->text('last_modified')->nullable();
            $table->text('change_frequency')->nullable();
            $table->double('priority')->nullable();
            $table->integer('status')->default(1);
            $table->integer('sitemap_enable')->default(1);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->integer('id', true); // Postgres serial
            $table->string('key')->unique('settings_key_unique');
            $table->text('value');
            $table->text('type')->default(new Expression("('public')"));
            $table->text('group')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('seos');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('notes');
        Schema::dropIfExists('email_templates');
        Schema::dropIfExists('contact_messages');
    }
};
