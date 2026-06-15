<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ScrapeVideoJob;
use Illuminate\Support\Facades\Log;

class RotanBatch extends Command
{
    protected $signature = 'rotan:batch {file : Path to file containing YouTube URLs, one per line}';
    
    protected $description = 'Dispatch ScrapeVideoJob for each URL in a given file';

    public function handle(): int
    {
        $filePath = $this->argument('file');

        if (!file_exists($filePath) || !is_readable($filePath)) {
            $this->error("File not found or not readable: {$filePath}");
            return Command::FAILURE;
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        if ($lines === false || count($lines) === 0) {
            $this->warn("No URLs found in file: {$filePath}");
            return Command::SUCCESS;
        }

        $dispatched = 0;
        $skipped = 0;

        foreach ($lines as $line) {
            $url = trim($line);
            
            if (empty($url)) {
                continue;
            }

            try {
                ScrapeVideoJob::dispatch($url);
                $dispatched++;
                Log::info("Dispatched ScrapeVideoJob for URL: {$url}");
            } catch (\Exception $e) {
                $this->error("Failed to dispatch job for URL: {$url} — {$e->getMessage()}");
                $skipped++;
            }
        }

        $this->info("✓ Berhasil mendispatch {$dispatched} jobs ke antrian.");
        
        if ($skipped > 0) {
            $this->warn("⚠ {$skipped} URL gagal didispatch.");
        }

        $this->line("Jalankan 'php artisan queue:work' untuk memproses antrian.");
        
        return Command::SUCCESS;
    }
}
