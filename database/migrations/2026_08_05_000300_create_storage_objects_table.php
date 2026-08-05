<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('storage_objects', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('bucket_id')->constrained('storage_buckets')->onDelete('cascade');
            // Client-visible S3 path, e.g. "documents/contracts/agreement.pdf".
            // Folders are purely virtual - derived from '/' prefixes at
            // query time, never persisted as their own rows.
            $table->string('object_key');
            $table->string('original_filename');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->char('checksum', 32)->nullable()->comment('MD5 of the object body, also served as ETag.');
            // Physical path relative to buckets/{user_id}/{bucket_name}/,
            // e.g. "2026/07/27/a8f3c7d9e5f24b1c.pdf" - see Database Storage
            // Strategy in docs/local/prd.md. Never store the full path.
            $table->string('relative_storage_path');
            $table->json('metadata_json')->nullable()->comment('Arbitrary client-supplied x-amz-meta-* key/values only.');
            $table->timestamps();

            $table->unique(['bucket_id', 'object_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('storage_objects');
    }
};
