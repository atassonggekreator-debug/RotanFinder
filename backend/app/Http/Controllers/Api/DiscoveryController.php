<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Services\ClipPotentialService;
use App\Services\ScraperService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DiscoveryController extends Controller
{
    public function __construct(
        private ScraperService $scraper,
        private ClipPotentialService $scorer,
    ) {}

    private function mapConfidence(?string $rec): string
    {
        return match (strtoupper($rec ?? '')) {
            'HIGH' => 'high',
            'MEDIUM' => 'medium',
            default => 'low',
        };
    }

    private function formatVideo(Video $v): array
    {
        $score = (float) ($v->clip_potential_score ?? 0);
        $views = (int) ($v->view_count ?? 0);
        $likes = (int) ($v->like_count ?? 0);
        $comments = (int) ($v->comment_count ?? 0);
        $duration = (int) ($v->duration ?? 0);

        // Calculate breakdown from actual video data (0-10 each)
        $engagementRate = $views > 0 ? ($likes / $views) * 100 : 0;
        $engagement = min(10, round(($engagementRate / 2.5) * 10, 1));

        $hours = $duration > 0 ? max(1, $duration / 3600) : 1;
        $viewVelocity = $hours > 0 ? $views / $hours : 0;
        $velocity = min(10, round(($viewVelocity / 10000) * 10, 1));

        $longform = min(10, round(min(1, $duration / 3600) * 10, 1));

        $commentRate = $views > 0 ? ($comments / $views) * 100 : 0;
        $momentum = min(10, round((($engagementRate + $commentRate) / 3) * 10, 1));

        $breakdown = [
            'engagement' => $engagement,
            'velocity' => $velocity,
            'longform' => $longform,
            'momentum' => $momentum,
        ];

        // Reason bullets based on score signals
        $reasons = [];
        if ($score >= 7) $reasons[] = 'High engagement rate suggests strong audience retention';
        if ($score >= 4) $reasons[] = 'Above-average view velocity for this niche';
        if (($v->duration ?? 0) >= 480) $reasons[] = 'Long-form content with multiple clip angles';
        if (($v->like_count ?? 0) > 1000) $reasons[] = 'Strong like-to-view ratio indicates quality content';
        if (empty($reasons)) $reasons[] = 'Limited data — scoring based on available metadata';

        return [
            'id' => $v->id,
            'title' => $v->title ?? '',
            'url' => $v->url ?? '#',
            'platform' => match (Str::lower($v->extractor ?? '')) {
                'youtube' => 'youtube',
                'twitch' => 'twitch',
                'facebook' => 'facebook',
                'tiktok' => 'tiktok',
                default => 'youtube',
            },
            'creator' => $v->uploader ?? 'Unknown',
            'duration' => $v->duration ?? 0,
            'views' => (int) ($v->view_count ?? 0),
            'likes' => (int) ($v->like_count ?? 0),
            'comments' => (int) ($v->comment_count ?? 0),
            'score' => $score,
            'confidence' => $this->mapConfidence($v->recommendation),
            'thumbnail' => $v->thumbnail ?? '',
            'breakdown' => $breakdown,
            'reason' => $reasons,
            'clipAngles' => ['Highlight reel', 'Key insight moment'],
            'viralPotential' => min(10, max(0, (int) round($score * 1.5))),
            'monetizationPotential' => min(10, max(0, (int) round($score * 0.8))),
            'riskNote' => $score < 4 ? 'Low score — verify content quality before clipping' : null,
            'isShortlisted' => false,
        ];
    }

    public function index(): JsonResponse
    {
        $videos = Video::orderByDesc('clip_potential_score')
            ->orderByDesc('upload_date')
            ->get()
            ->map(fn(Video $v) => $this->formatVideo($v))
            ->values();

        return response()->json($videos);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'niche' => 'required|string|max:255',
            'keywords' => 'nullable|array',
            'keywords.*' => 'string|max:100',
            'platforms' => 'nullable|array',
            'platforms.*' => 'string|in:youtube,twitch,facebook,tiktok',
            'durationMin' => 'nullable|integer|min:60',
            'durationMax' => 'nullable|integer|min:60',
            'maxResults' => 'nullable|integer|min:1|max:20',
        ]);

        $niche = $validated['niche'];
        $keywords = $validated['keywords'] ?? [];
        $durationMin = $validated['durationMin'] ?? null;
        $durationMax = $validated['durationMax'] ?? null;
        $maxResults = $validated['maxResults'] ?? 5;

        $searchParts = [$niche, ...$keywords];
        $searchQuery = implode(' ', array_filter($searchParts));

        try {
            $result = $this->scraper->searchAndScrape($searchQuery, $maxResults);
        } catch (\Throwable $e) {
            return response()->json([
                'search_id' => null,
                'candidates' => [],
                'error' => $e->getMessage(),
            ], 500);
        }

        $result['videos']->each(function (Video $video) {
            if ($video->clip_potential_score === null) {
                $scores = $this->scorer->calculateClipPotential($video);
                $video->update(['clip_potential_score' => $scores['clip_potential_score']]);
            }
        });

        $candidates = $result['videos']
            ->filter(function (Video $v) use ($durationMin, $durationMax) {
                if ($durationMin && $v->duration < $durationMin) return false;
                if ($durationMax && $v->duration > $durationMax) return false;
                return true;
            })
            ->map(fn(Video $v) => $this->formatVideo($v))
            ->values();

        $searchId = 'disc_' . Str::random(12);

        return response()->json([
            'search_id' => $searchId,
            'candidates' => $candidates,
            'meta' => [
                'new' => $result['new'],
                'existing' => $result['existing'],
                'filtered' => $result['videos']->count() - $candidates->count(),
            ],
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        return response()->json([
            'search_id' => $id,
            'status' => 'completed',
            'candidates' => [],
        ]);
    }
}
