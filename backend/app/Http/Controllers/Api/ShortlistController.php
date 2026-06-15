<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shortlist;
use App\Models\Video;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class ShortlistController extends Controller
{
    /**
     * GET /api/shortlist — get all shortlisted videos (full data).
     */
    public function index(): JsonResponse
    {
        $shortlists = Shortlist::with('video')
            ->latest()
            ->get();

        $videos = $shortlists->pluck('video')->filter()->values();

        return response()->json(
            $videos->map(fn($v) => $this->formatVideo($v))
        );
    }

    /**
     * POST /api/shortlist — add video to shortlist.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'video_id' => 'required|integer|exists:videos,id',
        ]);

        $exists = Shortlist::where('video_id', $validated['video_id'])->exists();

        if (!$exists) {
            Shortlist::create(['video_id' => $validated['video_id']]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * DELETE /api/shortlist/{video_id} — remove video from shortlist.
     */
    public function destroy(int $videoId): JsonResponse
    {
        Shortlist::where('video_id', $videoId)->delete();

        return response()->json(['success' => true]);
    }

    /**
     * POST /api/shortlist/toggle — legacy toggle (add/remove) used by old frontend.
     * Kept for backward compatibility.
     */
    public function toggle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'candidate_id' => 'required|integer',
            'action'       => 'required|string|in:add,remove',
        ]);

        $videoId = $validated['candidate_id'];

        if ($validated['action'] === 'add') {
            $exists = Shortlist::where('video_id', $videoId)->exists();
            if (!$exists) {
                Shortlist::create(['video_id' => $videoId]);
            }
        } else {
            Shortlist::where('video_id', $videoId)->delete();
        }

        // Also sync session for backward compat
        $shortlist = Shortlist::pluck('video_id')->toArray();
        Session::put('shortlist', $shortlist);

        return response()->json([
            'success' => true,
            'data'    => $shortlist,
        ]);
    }

    /**
     * Format a Video record into the frontend candidate contract.
     */
    private function formatVideo(Video $v): array
    {
        $score = (float) ($v->clip_potential_score ?? 0);
        $views = (int) ($v->view_count ?? 0);
        $likes = (int) ($v->like_count ?? 0);
        $comments = (int) ($v->comment_count ?? 0);
        $duration = (int) ($v->duration ?? 0);
        $durationHrs = max(1, $duration / 3600);

        // Engagement: like-to-view ratio
        $engagementRate = $views > 0 ? ($likes / $views) * 100 : 0;
        $engagement = min(10, round(($engagementRate / 2.5) * 10, 1));

        // Velocity: views per hour
        $viewVelocity = $durationHrs > 0 ? $views / $durationHrs : 0;
        $velocity = min(10, round(($viewVelocity / 10000) * 10, 1));

        // Longform: duration contribution
        $longform = round(min(1, $duration / 3600) * 10, 1);

        // Momentum: comments + engagement combined
        $commentRate = $views > 0 ? ($comments / $views) * 100 : 0;
        $momentum = min(10, round((($engagementRate + $commentRate) / 3) * 10, 1));

        // Confidence level
        $confidence = $score >= 7 ? 'high' : ($score >= 4 ? 'medium' : 'low');

        // Reason bullets
        $reasons = [];
        if ($engagementRate > 2) $reasons[] = 'High engagement rate suggests strong audience retention';
        if ($viewVelocity > 5000) $reasons[] = 'Above-average view velocity for this niche';
        if ($duration >= 1200) $reasons[] = 'Long-form content with multiple clip angles';
        if ($engagementRate > 1.5) $reasons[] = 'Strong like-to-view ratio indicates quality content';

        return [
            'id'                   => $v->id,
            'title'                => $v->title ?? '',
            'url'                  => $v->url ?? '',
            'platform'             => 'youtube',
            'creator'              => $v->uploader ?? '',
            'duration'             => $duration,
            'views'                => $views,
            'likes'                => $likes,
            'comments'             => $comments,
            'thumbnail'            => $v->thumbnail ?? '',
            'score'                => $score,
            'confidence'           => $confidence,
            'breakdown'            => compact('engagement', 'velocity', 'longform', 'momentum'),
            'reason'               => $reasons,
            'clipAngles'           => ['Highlight reel', 'Key insight moment'],
            'viralPotential'       => min(10, max(1, (int) round($score * 1.2))),
            'monetizationPotential' => min(10, max(1, (int) round($score * 0.8))),
            'riskNote'             => null,
            'isShortlisted'        => true,
        ];
    }
}
