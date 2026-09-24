<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('blogs', function (Blueprint $table) {
            $table->integer('id', true); // Postgres serial
            $table->string('slug')->unique('blogs_slug_unique'); // unique, so VARCHAR
            $table->text('title');
            $table->text('excerpt');
            $table->longText('body'); // Next `text` is unbounded; rich-text HTML can exceed TEXT's 64KB
            $table->text('category');
            $table->text('image')->nullable()->default(new Expression("('')"));
            $table->text('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blogs');
    }
};
