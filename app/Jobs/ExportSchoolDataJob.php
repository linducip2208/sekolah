<?php

namespace App\Jobs;

use App\Models\SchoolDataExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ExportSchoolDataJob implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    public int $timeout = 3600;

    public function __construct(public int $exportId) {}

    public function handle(): void
    {
        $export = SchoolDataExport::find($this->exportId);
        if (!$export) return;

        $export->status     = 'processing';
        $export->started_at = now();
        $export->save();

        $tmpDir = storage_path('app/exports-tmp/' . $export->id);
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        try {
            $allTables = $this->discoverSchoolTables();
            $filter = $export->included_tables ?: null;
            $tables = $filter
                ? array_values(array_intersect($allTables, $filter))
                : $allTables;
            $totalRows = 0;

            foreach ($tables as $table) {
                $totalRows += $this->dumpTable($table, $export->school_id, $tmpDir);
            }

            // Metadata
            file_put_contents($tmpDir . '/_meta.json', json_encode([
                'school_id'   => $export->school_id,
                'exported_at' => now()->toIso8601String(),
                'tables'      => $tables,
                'row_count'   => $totalRows,
                'format'      => $export->format,
            ], JSON_PRETTY_PRINT));

            $zipPath = storage_path('app/exports/school-' . $export->school_id . '-' . Str::random(8) . '.zip');
            if (!is_dir(dirname($zipPath))) {
                mkdir(dirname($zipPath), 0755, true);
            }
            $this->zipDirectory($tmpDir, $zipPath);

            $relativePath = 'exports/' . basename($zipPath);

            $export->status          = 'completed';
            $export->file_path       = $relativePath;
            $export->file_size_bytes = filesize($zipPath) ?: null;
            $export->included_tables = $tables;
            $export->row_count       = $totalRows;
            $export->completed_at    = now();
            $export->expires_at      = now()->addDays(7);
            $export->save();
        } catch (\Throwable $e) {
            $export->status = 'failed';
            $export->error  = $e->getMessage();
            $export->save();
        } finally {
            $this->cleanupTmp($tmpDir);
        }
    }

    private function discoverSchoolTables(): array
    {
        $connection = DB::connection();
        $database = $connection->getDatabaseName();
        $rows = $connection->select(
            "SELECT DISTINCT TABLE_NAME FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = ? AND COLUMN_NAME = 'school_id'",
            [$database]
        );
        return collect($rows)
            ->map(fn($r) => $r->TABLE_NAME ?? $r->table_name ?? null)
            ->filter()
            ->values()
            ->all();
    }

    private function dumpTable(string $table, int $schoolId, string $tmpDir): int
    {
        $csvPath  = $tmpDir . '/' . $table . '.csv';
        $jsonPath = $tmpDir . '/' . $table . '.json';

        $count = 0;
        $columns = Schema::getColumnListing($table);
        $fpCsv = fopen($csvPath, 'w');
        $fpJson = fopen($jsonPath, 'w');
        fwrite($fpJson, "[\n");
        fputcsv($fpCsv, $columns);

        $first = true;
        DB::table($table)
            ->where('school_id', $schoolId)
            ->orderBy('id')
            ->chunk(1000, function ($rows) use (&$count, $columns, $fpCsv, $fpJson, &$first) {
                foreach ($rows as $row) {
                    $arr = (array) $row;
                    $line = [];
                    foreach ($columns as $c) {
                        $val = $arr[$c] ?? null;
                        $line[] = is_scalar($val) || $val === null ? $val : json_encode($val);
                    }
                    fputcsv($fpCsv, $line);
                    if (!$first) fwrite($fpJson, ",\n");
                    fwrite($fpJson, json_encode($arr, JSON_UNESCAPED_UNICODE));
                    $first = false;
                    $count++;
                }
            });

        fwrite($fpJson, "\n]");
        fclose($fpCsv);
        fclose($fpJson);
        return $count;
    }

    private function zipDirectory(string $dir, string $zipPath): void
    {
        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Cannot create zip at ' . $zipPath);
        }
        $items = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
        foreach ($items as $item) {
            if ($item->isDir()) continue;
            $local = ltrim(str_replace($dir, '', $item->getPathname()), '/\\');
            $zip->addFile($item->getPathname(), $local);
        }
        $zip->close();
    }

    private function cleanupTmp(string $dir): void
    {
        if (!is_dir($dir)) return;
        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($it as $file) {
            $file->isDir() ? @rmdir($file->getPathname()) : @unlink($file->getPathname());
        }
        @rmdir($dir);
    }
}
