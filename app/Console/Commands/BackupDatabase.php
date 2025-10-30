<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BackupDatabase extends Command
{
    protected $signature = 'db:backup {--keep-days=30 : Number of days to keep backups}';

    protected $description = 'Backup the database and clean old backups';

    public function handle(): int
    {
        if (! config('database.backup.enabled', true)) {
            $this->info('Database backup is disabled.');

            return self::SUCCESS;
        }

        $this->info('Starting database backup...');

        $connection = config('database.default');
        $database = config("database.connections.{$connection}.database");
        $username = config("database.connections.{$connection}.username");
        $password = config("database.connections.{$connection}.password");
        $host = config("database.connections.{$connection}.host");

        $timestamp = now()->format('Y-m-d_H-i-s');
        $backupPath = config('database.backup.path', storage_path('backups'));
        $filename = "backup_{$database}_{$timestamp}.sql";
        $filepath = "{$backupPath}/{$filename}";

        // Create backup directory if it doesn't exist
        if (! is_dir($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        // Create backup using mysqldump
        $command = sprintf(
            'mysqldump --user=%s --password=%s --host=%s %s > %s',
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($host),
            escapeshellarg($database),
            escapeshellarg($filepath)
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            $this->error('Database backup failed!');

            return self::FAILURE;
        }

        // Compress the backup
        $gzipCommand = sprintf('gzip %s', escapeshellarg($filepath));
        exec($gzipCommand);

        $this->info("Database backup created: {$filename}.gz");

        // Clean old backups
        $this->cleanOldBackups($backupPath, $this->option('keep-days'));

        return self::SUCCESS;
    }

    protected function cleanOldBackups(string $backupPath, int $keepDays): void
    {
        $this->info("Cleaning backups older than {$keepDays} days...");

        $files = glob("{$backupPath}/backup_*.sql.gz");
        $cutoffTime = now()->subDays($keepDays)->timestamp;
        $deletedCount = 0;

        foreach ($files as $file) {
            if (filemtime($file) < $cutoffTime) {
                unlink($file);
                $deletedCount++;
            }
        }

        $this->info("Deleted {$deletedCount} old backup(s).");
    }
}
