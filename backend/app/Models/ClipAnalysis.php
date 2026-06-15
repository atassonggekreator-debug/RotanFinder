<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClipAnalysis extends Model
{
    protected $fillable = [
        'video_id',
        'engagement_score', 'velocity_score', 'longform_score', 'trend_score',
        'clip_potential_score',
        'key_topics', 'suggested_timestamps',
    ];

    protected $casts = [
        'engagement_score' => 'decimal:2',
        'velocity_score' => 'decimal:2',
        'longform_score' => 'decimal:2',
        'trend_score' => 'decimal:2',
        'clip_potential_score' => 'decimal:2',
        'key_topics' => 'array',
        'suggested_timestamps' => 'array',
    ];

    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }
}
