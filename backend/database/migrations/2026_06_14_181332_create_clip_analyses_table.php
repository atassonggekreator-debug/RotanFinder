<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clip_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('video_id')->constrained()->cascadeOnDelete();
            $table->decimal('engagement_score', 5, 2)->nullable();
            $table->decimal('velocity_score', 5, 2)->nullable();
            $table->decimal('longform_score', 5, 2)->nullable();
            $table->decimal('trend_score', 5, 2)->nullable();
            $table->decimal('clip_potential_score', 5, 2)->nullable();
            $table->json('key_topics')->nullable();
            $table->json('suggested_timestamps')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clip_analyses');
    }
};
