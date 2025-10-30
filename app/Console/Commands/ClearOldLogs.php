<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ClearOldLogs extends Command
{
    protected $signature = 'log:clear {--days=30 : Number of days to keep logs}';

    protected $description = 'Clear old log files';

    public function handle(): int
    {
        $days = $this->option('days');
        $logPath = storage_path('logs');
        $cutoffTime = now()->subDays($days)->timestamp;
        $deletedCount = 0;

        $this->info("Clearing log files older than {$days} days...");

        $files = glob("{$logPath}/*.log");

        foreach ($files as $file) {
            if (filemtime($file) < $cutoffTime && basename($file) !== 'laravel.log') {
                unlink($file);
                $deletedCount++;
                $this->line("Deleted: " . basename($file));
            }
        }

        $this->info("Deleted {$deletedCount} old log file(s).");

        return self::SUCCESS;
    }
}
