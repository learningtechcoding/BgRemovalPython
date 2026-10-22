<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('image_jobs', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->string('status', 20)->default('queued')->index();
            $table->string('original_storage_key')->nullable();
            $table->string('result_storage_key')->nullable();
            $table->string('original_mime_type', 50);
            $table->unsignedBigInteger('original_size_bytes');
            $table->unsignedInteger('original_width');
            $table->unsignedInteger('original_height');
            $table->unsignedInteger('result_width')->nullable();
            $table->unsignedInteger('result_height')->nullable();
            $table->string('provider_name', 30)->nullable();
            $table->string('error_code', 40)->nullable();
            $table->timestamp('processing_started_at')->nullable();
            $table->timestamp('processing_completed_at')->nullable();
            $table->timestamp('expires_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('image_jobs');
    }
};
