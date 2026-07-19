<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class RestoreDatabase extends Command
{
    protected $signature   = 'eschool:restore {file : Backup filename in db-backups/}';
    protected $description = 'Restore MySQL DB from a backup file';

    public function handle(): int
    {
        $diskName  = config('eschool.backup.disk', env('BACKUP_DISK', 'local'));
        $remoteFile = 'db-backups/' . $this->argument('file');

        if (!Storage::disk($diskName)->exists($remoteFile)) {
            $this->error("File not found: {$diskName}://{$remoteFile}");
            return self::FAILURE;
        }

        if (!$this->confirm('⚠️ This will OVERWRITE current database. Continue?', false)) {
            return self::FAILURE;
        }

        $tmpFile = storage_path('app/restore-tmp.sql');
        file_put_contents($tmpFile, Storage::disk($diskName)->get($remoteFile));

        if (str_ends_with($remoteFile, '.enc')) {
            $decFile = $tmpFile . '.dec';
            Process::fromShellCommandline(sprintf(
                'openssl enc -aes-256-cbc -d -salt -pbkdf2 -in %s -out %s -pass env:BACKUP_ENCRYPTION_PASSWORD',
                escapeshellarg($tmpFile), escapeshellarg($decFile),
            ))->mustRun();
            unlink($tmpFile);
            $tmpFile = $decFile;
        }

        $cmd = sprintf(
            'mysql -h%s -P%s -u%s -p%s %s < %s',
            escapeshellarg(config('database.connections.mysql.host')),
            escapeshellarg(config('database.connections.mysql.port')),
            escapeshellarg(config('database.connections.mysql.username')),
            escapeshellarg(config('database.connections.mysql.password')),
            escapeshellarg(config('database.connections.mysql.database')),
            escapeshellarg($tmpFile),
        );

        $proc = Process::fromShellCommandline($cmd);
        $proc->setTimeout(1800);
        $proc->run();

        unlink($tmpFile);

        if (!$proc->isSuccessful()) {
            $this->error('Restore failed: ' . $proc->getErrorOutput());
            return self::FAILURE;
        }

        $this->info('✓ Database restored successfully');
        $this->warn('Run: php artisan cache:clear && php artisan queue:restart');

        return self::SUCCESS;
    }
}
