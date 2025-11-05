<?php

namespace App\Filament\Pages;


use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class BackupDatabase extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-circle-stack';

    protected static string | \UnitEnum | null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Backup Database';

    protected static ?string $title = 'Backup Database';

    protected static ?int $navigationSort = 99;

    protected string $view = 'filament.pages.backup-database';

    public static function canAccess(): bool
    {
        return Auth::check() && Auth::user()->can('page_BackupDatabase');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('backupNow')
                ->label('Backup Sekarang')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Backup Database')
                ->modalDescription('Apakah Anda yakin ingin membuat backup database sekarang?')
                ->modalSubmitActionLabel('Ya, Backup')
                ->action(function (): void {
                    try {
                        $this->createBackup();

                        Notification::make()
                            ->title('Backup Berhasil')
                            ->body('Database berhasil di-backup.')
                            ->success()
                            ->icon('heroicon-o-check-circle')
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Backup Gagal')
                            ->body('Terjadi kesalahan: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }

    protected function createBackup(): void
    {
        $timestamp = now()->format('Y-m-d-H-i-s');
        $filename = "backup-{$timestamp}.sql";
        $backupPath = storage_path("app/backups/{$filename}");

        // Ensure backup directory exists
        if (!file_exists(storage_path('app/backups'))) {
            mkdir(storage_path('app/backups'), 0755, true);
        }

        $dbConnection = config('database.default');
        $dbConfig = config("database.connections.{$dbConnection}");

        if ($dbConfig['driver'] === 'sqlite') {
            // For SQLite, just copy the database file
            $dbPath = database_path($dbConfig['database']);
            copy($dbPath, $backupPath);
        } else {
            // For MySQL/PostgreSQL - try mysqldump first
            $command = $this->getBackupCommand($dbConfig, $backupPath);
            exec($command . ' 2>&1', $output, $returnVar);

            if ($returnVar !== 0) {
                // If mysqldump fails, try using PHP to create backup
                $this->createBackupUsingPHP($dbConfig, $backupPath);
            }
            
            // Check if file was created and has content
            if (!file_exists($backupPath) || filesize($backupPath) === 0) {
                throw new \Exception('Backup file is empty or was not created. Error: ' . implode("\n", $output));
            }
        }
    }

    protected function createBackupUsingPHP(array $dbConfig, string $backupPath): void
    {
        $database = $dbConfig['database'];
        $tables = \DB::select('SHOW TABLES');
        $tableKey = 'Tables_in_' . $database;
        
        $sql = "-- MySQL Backup\n";
        $sql .= "-- Generated: " . now() . "\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
        
        foreach ($tables as $table) {
            $tableName = $table->$tableKey;
            
            // Get CREATE TABLE statement
            $createTable = \DB::select("SHOW CREATE TABLE `{$tableName}`");
            $sql .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
            $sql .= $createTable[0]->{'Create Table'} . ";\n\n";
            
            // Get table data
            $rows = \DB::table($tableName)->get();
            if ($rows->count() > 0) {
                foreach ($rows as $row) {
                    $values = array_map(function($value) {
                        return is_null($value) ? 'NULL' : "'" . addslashes($value) . "'";
                    }, (array) $row);
                    
                    $sql .= "INSERT INTO `{$tableName}` VALUES (" . implode(', ', $values) . ");\n";
                }
                $sql .= "\n";
            }
        }
        
        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
        
        file_put_contents($backupPath, $sql);
    }

    protected function getBackupCommand(array $dbConfig, string $backupPath): string
    {
        $driver = $dbConfig['driver'];
        $host = $dbConfig['host'];
        $port = $dbConfig['port'];
        $database = $dbConfig['database'];
        $username = $dbConfig['username'];
        $password = $dbConfig['password'];

        if ($driver === 'mysql') {
            // For Windows, we need to handle the password differently
            if (empty($password)) {
                return sprintf(
                    'mysqldump --host=%s --port=%s --user=%s %s > "%s"',
                    $host,
                    $port,
                    $username,
                    $database,
                    $backupPath
                );
            } else {
                return sprintf(
                    'mysqldump --host=%s --port=%s --user=%s --password=%s %s > "%s"',
                    $host,
                    $port,
                    $username,
                    $password,
                    $database,
                    $backupPath
                );
            }
        } elseif ($driver === 'pgsql') {
            return sprintf(
                'set PGPASSWORD=%s && pg_dump --host=%s --port=%s --username=%s --dbname=%s --file="%s"',
                $password,
                $host,
                $port,
                $username,
                $database,
                $backupPath
            );
        }

        throw new \Exception("Unsupported database driver: {$driver}");
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama File')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color('warning')
                    ->formatStateUsing(fn() => 'manual'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color('success')
                    ->formatStateUsing(fn() => 'success'),

                Tables\Columns\TextColumn::make('size')
                    ->label('Ukuran')
                    ->formatStateUsing(fn($state) => $this->formatBytes($state)),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i:s')
                    ->sortable(),
            ])
            ->recordAction(null)
            ->recordUrl(null)
            ->actions([
                Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('warning')
                    ->action(function ($record) {
                        $fileName = is_array($record) ? $record['name'] : $record->name;
                        $filePath = storage_path("app/backups/{$fileName}");
                        
                        if (!file_exists($filePath)) {
                            Notification::make()
                                ->danger()
                                ->title('File Tidak Ditemukan')
                                ->body('File backup tidak dapat ditemukan.')
                                ->send();
                            return null;
                        }

                        return response()->download($filePath);
                    }),

                Action::make('delete')
                    ->label('Hapus')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Backup')
                    ->modalDescription('Apakah Anda yakin ingin menghapus backup ini?')
                    ->modalSubmitActionLabel('Ya, Hapus')
                    ->action(function ($record) {
                        $fileName = is_array($record) ? $record['name'] : $record->name;
                        $filePath = storage_path("app/backups/{$fileName}");
                        
                        if (file_exists($filePath)) {
                            unlink($filePath);
                        }
                        
                        Notification::make()
                            ->success()
                            ->title('Backup Dihapus')
                            ->body('File backup berhasil dihapus.')
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Belum Ada Backup')
            ->emptyStateDescription('Buat backup pertama Anda dengan klik tombol "Backup Sekarang" di atas.')
            ->emptyStateIcon('heroicon-o-circle-stack')
            ->paginated([10, 25, 50]);
    }

    public function getTableRecords(): Collection
    {
        return $this->getBackupFiles();
    }

    public function getTableRecordKey($record): string
    {
        return is_array($record) ? $record['name'] : $record->name;
    }

    protected function getTableQuery(): ?\Illuminate\Database\Eloquent\Builder
    {
        return null;
    }

    public function getTableRecord($key): ?array
    {
        return $this->getBackupFiles()->firstWhere('__key', $key);
    }

    protected function getBackupFiles(): Collection
    {
        $backupPath = storage_path('app/backups');
        
        if (!file_exists($backupPath)) {
            return collect();
        }

        $files = array_diff(scandir($backupPath), ['.', '..']);
        
        return collect($files)->map(function ($file) use ($backupPath) {
            $filePath = $backupPath . '/' . $file;
            return [
                '__key' => $file,
                'name' => $file,
                'size' => filesize($filePath),
                'created_at' => \Carbon\Carbon::createFromTimestamp(filemtime($filePath)),
                'type' => 'manual',
                'status' => 'success',
            ];
        })->sortByDesc('created_at')->values();
    }

    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
