<?php

namespace App\Services;

use App\Models\Video;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

class ScraperService
{
    private int $timeout = 60;
    private int $minDuration = 480; // 8 minutes in seconds

    /**
     * Search YouTube via yt-dlp and scrape results.
     *
     * @return array{new: int, existing: int, videos: \Illuminate\Support\Collection}
     */
    public function searchAndScrape(string $query, int $maxResults = 5): array
    {
        $searchCmd = "yt-dlp ytsearch{$maxResults}:" . escapeshellarg($query)
            . " --no-cookies --dump-json --no-download --remote-components ejs:github";

        $result = Process::timeout(120)->run($searchCmd);

        if (!$result->successful()) {
            throw new \RuntimeException(
                "yt-dlp search failed: " . ($result->errorOutput() ?: $result->output())
            );
        }

        $lines = array_filter(explode("\n", $result->output()));
        $videos = collect();
        $newCount = 0;
        $existingCount = 0;

        foreach ($lines as $line) {
            $json = json_decode($line, true);
            if (!$json || empty($json['webpage_url'])) continue;

            $url = $json['webpage_url'];

            // Check duplicate
            $existing = Video::where('url', $url)->first();
            if ($existing) {
                $existingCount++;
                $videos->push($existing);
                continue;
            }

            // Skip shorts
            $duration = (int) ($json['duration'] ?? 0);
            if ($duration > 0 && $duration < $this->minDuration) continue;

            // Save
            $metadata = $this->extractMetadata($json);
            $video = Video::create($metadata);
            $newCount++;
            $videos->push($video);
        }

        return [
            'new' => $newCount,
            'existing' => $existingCount,
            'videos' => $videos,
        ];
    }

    /**
     * Scrape metadata from a video URL using yt-dlp.
     *
     * @param string $url
     * @return array{status: string, video: Video|null, metadata: array}
     * @throws \RuntimeException
     */
    public function scrape(string $url): array
    {
        // Check for duplicates first
        $existing = Video::where('url', $url)->first();
        if ($existing) {
            Log::info("Duplicate URL skipped: {$url}", ['video_id' => $existing->id]);
            return [
                'status' => 'duplicate',
                'video' => $existing,
                'metadata' => $existing->toArray(),
            ];
        }

        // Run yt-dlp
        $result = Process::timeout($this->timeout)
            ->run("yt-dlp --dump-json --no-download --remote-components ejs:github " . escapeshellarg($url));

        if (!$result->successful()) {
            $error = $result->errorOutput() ?: $result->output();
            Log::error("yt-dlp failed for URL: {$url}", ['error' => $error]);
            throw new \RuntimeException("yt-dlp failed: {$error}");
        }

        $json = json_decode($result->output(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("Failed to parse yt-dlp JSON: " . json_last_error_msg());
        }

        // Extract & map metadata
        $metadata = $this->extractMetadata($json);

        // Validate duration (reject shorts)
        if (($metadata['duration'] ?? 0) > 0 && $metadata['duration'] < $this->minDuration) {
            Log::info("Short video rejected ({$metadata['duration']}s): {$url}");
            throw new \RuntimeException("Video too short: {$metadata['duration']}s (min {$this->minDuration}s)");
        }

        // Save to database
        $video = Video::create($metadata);

        Log::info("Video scraped successfully", [
            'id' => $video->id,
            'title' => $video->title,
            'url' => $url,
        ]);

        return [
            'status' => 'new',
            'video' => $video,
            'metadata' => $video->toArray(),
        ];
    }

    /**
     * Map yt-dlp JSON keys to our schema.
     */
    private function extractMetadata(array $json): array
    {
        return [
            'url'           => $json['webpage_url'] ?? '',
            'video_id'      => $json['id'] ?? null,
            'title'         => $json['title'] ?? null,
            'description'   => mb_substr($json['description'] ?? '', 0, 5000),
            'duration'      => (int) ($json['duration'] ?? 0),
            'view_count'    => (int) ($json['view_count'] ?? 0),
            'like_count'    => (int) ($json['like_count'] ?? 0),
            'comment_count' => (int) ($json['comment_count'] ?? 0),
            'uploader'      => $json['channel'] ?? $json['uploader'] ?? null,
            'extractor'     => $json['extractor'] ?? 'youtube',
            'thumbnail'     => $json['thumbnail'] ?? null,
            'upload_date'   => isset($json['upload_date'])
                ? \Carbon\Carbon::createFromFormat('Ymd', $json['upload_date'])->format('Y-m-d')
                : null,
            'status'        => 'scraped',
        ];
    }
}
