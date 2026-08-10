<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('storage_api_keys', function (Blueprint $table) {
            $table->string('title')->nullable()->after('user_id');
            $table->foreignId('bucket_id')->nullable()->after('title')
                ->constrained('storage_buckets')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('storage_api_keys', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bucket_id');
            $table->dropColumn('title');
        });
    }
};
