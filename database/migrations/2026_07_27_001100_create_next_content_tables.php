<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('contact_messages')) {
            Schema::create('contact_messages', function (Blueprint $table) {
                $table->id();
                $table->char('user_id', 36)->nullable();
                $table->string('to_user');
                $table->string('subject');
                $table->text('message');
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            });
        }

        if (! Schema::hasTable('email_templates')) {
            Schema::create('email_templates', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->string('title')->nullable();
                $table->string('subject');
                $table->text('body');
                $table->text('params')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('notes')) {
            Schema::create('notes', function (Blueprint $table) {
                $table->id();
                $table->char('user_id', 36);
                $table->string('title');
                $table->text('note')->nullable();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        if (! Schema::hasTable('pages')) {
            Schema::create('pages', function (Blueprint $table) {
                $table->id();
                $table->string('slug')->unique();
                $table->string('title');
                $table->longText('body');
                $table->string('meta_title')->nullable();
                $table->text('meta_description')->nullable();
                $table->string('status')->default('inactive');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('seos')) {
            Schema::create('seos', function (Blueprint $table) {
                $table->id();
                $table->string('type')->default('STATIC');
                $table->string('url')->unique();
                $table->string('title')->nullable();
                $table->string('meta_title')->nullable();
                $table->string('keyword')->nullable();
                $table->text('meta_keyword')->nullable();
                $table->text('description')->nullable();
                $table->text('meta_description')->nullable();
                $table->string('image')->nullable();
                $table->string('canonical')->nullable();
                $table->string('last_modified')->nullable();
                $table->string('change_frequency')->nullable();
                $table->double('priority')->nullable();
                $table->integer('status')->default(1);
                $table->integer('sitemap_enable')->default(1);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value');
                $table->string('type')->default('public');
                $table->string('group')->nullable();
                $table->timestamps();
            });
        }
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
