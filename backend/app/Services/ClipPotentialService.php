<?php

namespace App\Services;

use App\Models\Video;

class ClipPotentialService
{
    // Normalization ceilings (Path 3 - MVP-calibrated)
    private const MAX_ENGAGEMENT_RATE = 2.5;      // 2.5% engagement = perfect score (10)
    private const MAX_VELOCITY        = 10000;    // 10K views/hr = perfect score (10)
    private const MAX_LONGFORM_VALUE  = 25;       // (10min × 2.5%) = 25 = perfect score (10)

    // Weights
    private const W_ENGAGEMENT = 0.40;
    private const W_VELOCITY   = 0.30;
    private const W_LONGFORM  = 0.20;
    private const W_MOMENTUM   = 0.10;

    /**
     * Calculate clip potential for a video.
     *
     * @return array{
     *     engagement_score: float,
     *     velocity_score: float,
     *     longform_score: float,
     *     trend_score: float,
     *     clip_potential_score: float,
     *     recommendation: string,
     *     breakdown: array
     * }
     */
    public function calculateClipPotential(Video $video): array
    {
        // Engagement Rate = likes / views × 100
        $engagementRate = $video->view_count > 0
            ? ($video->like_count / $video->view_count) * 100
            : 0.0;

        // View Velocity = views / hours_since_upload
        $hoursSinceUpload = $this->hoursSinceUpload($video);
        $velocity = $hoursSinceUpload > 0
            ? $video->view_count / $hoursSinceUpload
            : $video->view_count; // fallback: treat as 1 hour

        // Long-form Value = (duration_minutes / 10) × engagement_rate
        $durationMinutes = ($video->duration ?? 0) / 60;
        $longformValue = ($durationMinutes / 10) * $engagementRate;

        // Trend Momentum: velocity ratio vs DB average (proxy)
        $avgVelocity = self::getAverageVelocity();
        $trendScore = ($avgVelocity > 0 && $velocity > 0)
            ? min(log10($velocity / max($avgVelocity, 1)) + 1, 2)  // centered at 1, range 0–2
            : 1.0;
        // Scale to 0-10
        $trendScore = $trendScore * 5; // 1.0 → 5, 2.0 → 10

        // Normalize each to 0-10
        $engagementScore = $this->normalize($engagementRate, 0, self::MAX_ENGAGEMENT_RATE);
        $velocityScore   = $this->normalize($velocity, 0, self::MAX_VELOCITY);
        $longformScore   = $this->normalize($longformValue, 0, self::MAX_LONGFORM_VALUE);

        // Weighted final score
        $clipPotentialScore =
            ($engagementScore * self::W_ENGAGEMENT)
            + ($velocityScore   * self::W_VELOCITY)
            + ($longformScore   * self::W_LONGFORM)
            + ($trendScore      * self::W_MOMENTUM);

        // Recommendation tier
        $recommendation = match (true) {
            $clipPotentialScore >= 7.0 => 'HIGH',
            $clipPotentialScore >= 4.0 => 'MEDIUM',
            default => 'LOW',
        };

        return [
            'engagement_score'    => round($engagementScore, 2),
            'velocity_score'      => round($velocityScore, 2),
            'longform_score'      => round($longformScore, 2),
            'trend_score'         => round($trendScore, 2),
            'clip_potential_score'=> round($clipPotentialScore, 2),
            'recommendation'      => $recommendation,
            'breakdown' => [
                'engagement_rate'   => round($engagementRate, 4),
                'velocity'          => round($velocity, 2),
                'longform_value'    => round($longformValue, 4),
                'hours_since_upload'=> round($hoursSinceUpload, 1),
            ],
        ];
    }

    /**
     * Normalize value to 0-10 range.
     */
    private function normalize(float $value, float $min, float $max): float
    {
        if ($max <= $min) {
            return 0.0;
        }
        $normalized = ($value - $min) / ($max - $min);
        return max(0.0, min(10.0, $normalized * 10));
    }

    /**
     * Hours since video was uploaded.
     */
    private function hoursSinceUpload(Video $video): float
    {
        if (!$video->upload_date) {
            return 24.0; // default fallback: assume 1 day old
        }

        $uploadedAt = $video->upload_date->startOfDay();
        $now = now()->startOfDay();

        $diffInDays = max(0, $now->diffInDays($uploadedAt, false) * -1);

        return max(1.0, $diffInDays * 24); // minimum 1 hour to avoid div-by-zero
    }

    /**
     * Get average velocity across all videos in DB (for trend proxy).
     */
    private function getAverageVelocity(): float
    {
        $videos = Video::whereNotNull('upload_date')
            ->where('view_count', '>', 0)
            ->get();

        if ($videos->isEmpty()) {
            return 100.0; // sensible default
        }

        $totalVelocity = 0;
        foreach ($videos as $video) {
            $hours = $this->hoursSinceUpload($video);
            $totalVelocity += ($video->view_count / $hours);
        }

        return $totalVelocity / $videos->count();
    }

    /**
     * Recalculate scores for all existing videos and update DB.
     *
     * @return int Number of videos updated
     */
    public function recalculateAll(): int
    {
        $videos = Video::where('status', 'scraped')->get();
        $count = 0;

        foreach ($videos as $video) {
            $scores = $this->calculateClipPotential($video);
            $video->update(['clip_potential_score' => $scores['clip_potential_score']]);
            $count++;
        }

        return $count;
    }
}