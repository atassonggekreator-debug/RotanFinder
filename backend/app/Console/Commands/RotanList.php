<?php

namespace App\Console\Commands;

use App\Models\Video;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class RotanList extends Command
{
    // Signature: rotan:list dengan optional flag --filter= dan --exclude-scraped
    protected $signature = 'rotan:list {--filter= : Filter recommendation (high, medium, low)}
                                      {--exclude-scraped : Hanya tampilkan video yang belum pernah di-scrape sebelumnya}';

    protected $description = 'List all scraped videos with their clip potential scores';

    public function handle()
    {
        $query = Video::query();

        // Handle exclude-scraped: exclude jika URL sudah ada di DB >24 jam
        if ($this->option('exclude-scraped')) {
            $query->where('created_at', '>=', now()->subHours(24));
        }

        // Handle filter
        if ($filter = $this->option('filter')) {
            $filter = strtolower($filter);
            if ($filter === 'high') {
                $query->where('clip_potential_score', '>=', 7.0);
            } elseif ($filter === 'medium') {
                $query->where('clip_potential_score', '>=', 4.0)->where('clip_potential_score', '<', 7.0);
            } elseif ($filter === 'low') {
                $query->where(function($q) {
                    $q->where('clip_potential_score', '<', 4.0)
                      ->orWhereNull('clip_potential_score');
                });
            } else {
                $this->error("Invalid filter. Use high, medium, or low.");
                return Command::FAILURE;
            }
        }

        // Sorting: Score DESC, Upload Date DESC
        $videos = $query->orderByDesc('clip_potential_score')
                        ->orderByDesc('upload_date')
                        ->get();

        if ($videos->isEmpty()) {
            $this->info("Tidak ada video yang ditemukan.");
            return Command::SUCCESS;
        }

        $headers = ['ID', 'Platform', 'Title', 'Duration (min)', 'Views', 'Score', 'Recommendation'];
        
        $rows = $videos->map(function ($video) {
            // Str::limit untuk max 40 chars
            $title = Str::limit($video->title ?? 'Unknown', 40);
            
            $durationMin = $video->duration ? round($video->duration / 60) : 0;
            $views = number_format($video->view_count);
            $score = $video->clip_potential_score !== null ? number_format($video->clip_potential_score, 1) : 'N/A';
            
            // Color coding recommendation
            $rec = $video->recommendation;
            if ($rec === 'HIGH') {
                $recFormatted = "<info>{$rec}</info>"; // Hijau
            } elseif ($rec === 'MEDIUM') {
                $recFormatted = "<comment>{$rec}</comment>"; // Kuning
            } else {
                $recFormatted = "<error>{$rec}</error>"; // Merah
            }

            return [
                $video->id,
                ucfirst($video->extractor),
                $title,
                $durationMin,
                $views,
                $score,
                $recFormatted
            ];
        })->toArray();

        $this->table($headers, $rows);

        return Command::SUCCESS;
    }
}
