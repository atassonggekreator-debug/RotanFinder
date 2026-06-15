<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Video extends Model
{
    protected $fillable = [
        'url', 'video_id', 'title', 'description', 'duration',
        'view_count', 'like_count', 'comment_count',
        'uploader', 'extractor', 'thumbnail', 'upload_date',
        'clip_potential_score', 'status',
    ];

    protected $casts = [
        'duration' => 'integer',
        'view_count' => 'integer',
        'like_count' => 'integer',
        'comment_count' => 'integer',
        'clip_potential_score' => 'decimal:2',
        'upload_date' => 'date',
    ];

    // Accessor for recommendation string
    public function getRecommendationAttribute(): string
    {
        if ($this->clip_potential_score === null) {
            return 'N/A';
        }

        return match (true) {
            $this->clip_potential_score >= 7.0 => 'HIGH',
            $this->clip_potential_score >= 4.0 => 'MEDIUM',
            default => 'LOW',
        };
    }

    public function scopeHighPotential($query)
    {
        return $query->where('clip_potential_score', '>=', 7.0);
    }

    public function clipAnalysis(): HasOne
    {
        return $this->hasOne(ClipAnalysis::class);
    }
}
