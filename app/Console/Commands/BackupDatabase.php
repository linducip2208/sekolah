<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class BackupDatabase extends Command
{
    protected $signature   = 'eschool:backup {--encrypt : Encrypt with AES-256}';
    protected $description = 'Backup MySQL DB + storage to configured backup disk';

    public function handle(): int
    {
        $timestamp = now()->format('Y-m-d-His');
        $tmpFile   = storage_path("app/backup-tmp-{$timestamp}.sql");
        $diskName  = config('eschool.backup.disk', env('BACKUP_DISK', 'local'));

        $this->info("Backing up database to {$tmpFile}...");

        $cmd = sprintf(
            'mysqldump -h%s -P%s -u%s -p%s --single-transaction --quick --no-tablespaces %s > %s',
            escapeshellarg(config('database.connections.mysql.host')),
            escapeshellarg(config('database.connections.mysql.port')),
            escapeshellarg(config('database.connections.mysql.username')),
            escapeshellarg(config('database.connections.mysql.password')),
            escapeshellarg(config('database.connections.mysql.database')),
            escapeshellarg($tmpFile),
        );

        $proc = Process::fromShellCommandline($cmd);
        $proc->setTimeout(900);
        $proc->run();

        if (!$proc->isSuccessful()) {
            $this->error('mysqldump failed: ' . $proc->getErrorOutput());
            return self::FAILURE;
        }

        $size = filesize($tmpFile);
        $this->info(sprintf('Dump size: %.2f MB', $size / 1024 / 1024));

        if ($this->option('encrypt') && env('BACKUP_ENCRYPTION_PASSWORD')) {
            $encFile = $tmpFile . '.enc';
            $encCmd  = sprintf(
                'openssl enc -aes-256-cbc -salt -pbkdf2 -in %s -out %s -pass env:BACKUP_ENCRYPTION_PASSWORD',
                escapeshellarg($tmpFile),
                escapeshellarg($encFile),
            );
            Process::fromShellCommandline($encCmd)->mustRun();
            unlink($tmpFile);
            $tmpFile = $encFile;
            $this->info('Encrypted with AES-256-CBC');
        }

        $remoteName = 'db-backups/' . basename($tmpFile);
        Storage::disk($diskName)->put($remoteName, file_get_contents($tmpFile));
        unlink($tmpFile);

        $this->info("✓ Backup uploaded: {$diskName}://{$remoteName}");

        $this->pruneOldBackups($diskName);

        return self::SUCCESS;
    }

    protected function pruneOldBackups(string $diskName): void
    {
        $retention = (int) env('BACKUP_RETENTION_DAYS', 30);
        $cutoff    = now()->subDays($retention)->timestamp;

        $files = Storage::disk($diskName)->files('db-backups');
        $deleted = 0;

        foreach ($files as $f) {
            if (Storage::disk($diskName)->lastModified($f) < $cutoff) {
                Storage::disk($diskName)->delete($f);
                $deleted++;
            }
        }

        if ($deleted > 0) {
            $this->info("Pruned {$deleted} backups older than {$retention} days");
        }
    }
}
