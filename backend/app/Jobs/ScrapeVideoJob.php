<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\ScraperService;
use App\Services\ClipPotentialService;
use Illuminate\Support\Facades\Log;

class ScrapeVideoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [10, 30, 60];
    
    public $timeout = 120; // 2 menit per job

    private string $url;

    public function __construct(string $url)
    {
        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException("Invalid URL: {$url}");
        }
        $this->url = $url;
    }

    public function handle(ScraperService $scraperService, ClipPotentialService $clipService): void
    {
        Log::info("ScrapeVideoJob processing: {$this->url}");
        
        // 1. Scrape metadata & save to DB
        $result = $scraperService->scrape($this->url);
        $video = $result['video'];

        // 2. Calculate clip potential score
        $scoreData = $clipService->calculateClipPotential($video);

        // 3. Update video with score
        $video->update([
            'clip_potential_score' => $scoreData['clip_potential_score'],
        ]);

        // 4. Log hasil scoring
        Log::info("Video {$video->id} scored: {$scoreData['clip_potential_score']} ({$scoreData['recommendation']})", [
            'title' => $video->title,
            'url' => $this->url,
            'breakdown' => $scoreData['breakdown'],
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::critical("ScrapeVideoJob permanently failed after {$this->tries} attempts", [
            'url' => $this->url,
            'error' => $exception->getMessage()
        ]);
    }
}
