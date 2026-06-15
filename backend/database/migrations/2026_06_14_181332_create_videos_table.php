<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->string('url')->unique();
            $table->string('video_id')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->integer('duration')->nullable(); // seconds
            $table->bigInteger('view_count')->default(0);
            $table->bigInteger('like_count')->default(0);
            $table->bigInteger('comment_count')->default(0);
            $table->string('uploader')->nullable();
            $table->string('extractor')->default('youtube');
            $table->string('thumbnail')->nullable();
            $table->date('upload_date')->nullable();
            $table->decimal('clip_potential_score', 5, 2)->nullable();
            $table->string('status')->default('pending'); // pending/scraped/analyzed/failed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
