<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('storage_buckets', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->enum('visibility', ['public', 'private'])->default('private');
            // Bucket ownership is exclusively a Bucket Admin (role USER)
            // concern - see Roles & Panels in docs/local/prd.md. FK is not
            // role-restricted at the DB level; the app layer enforces it.
            $table->char('user_id', 36)->charset('ascii')->collation('ascii_bin');
            $table->unsignedBigInteger('storage_quota')->nullable()->comment('Bytes. Schema field only - not enforced in v1.');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            // Globally unique, exactly like real S3 bucket names. Required
            // so an anonymous GET/HEAD on a Public bucket (see Public
            // Buckets in docs/local/prd.md) can resolve {bucket} from the
            // URL alone, with no access key to scope the lookup by owner.
            $table->unique('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('storage_buckets');
    }
};
