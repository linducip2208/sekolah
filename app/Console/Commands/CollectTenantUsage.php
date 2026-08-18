<?php

namespace App\Http\Console\Commands;

use App\Models\Saas\TenantUsage;
use App\Models\School;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CollectTenantUsage extends Command
{
    protected $signature = 'usage:collect {--month=}';
    protected $description = 'Aggregate tenant usage data per school per month';

    public function handle(): int
    {
        $month = $this->option('month') ?? now()->subMonth()->format('Y-m');
        $startDate = $month . '-01';
        $endDate = now()->create($month)->endOfMonth()->toDateString();

        $this->info("Collecting usage data for {$month}...");

        $schools = School::withoutGlobalScopes()->where('is_active', true)->get();
        $bar = $this->output->createProgressBar($schools->count());

        foreach ($schools as $school) {
            $activeStudents = User::where('school_id', $school->id)
                ->whereHas('roles', fn ($q) => $q->where('name', 'student'))
                ->where('is_active', true)
                ->count();

            $activeTeachers = User::where('school_id', $school->id)
                ->whereHas('roles', fn ($q) => $q->whereIn('name', ['teacher', 'admin', 'principal']))
                ->where('is_active', true)
                ->count();

            $totalLogins = DB::table('audit_logs')
                ->where('school_id', $school->id)
                ->where('event', 'login')
                ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->count();

            $apiCalls = DB::table('personal_access_tokens')
                ->where('name', '!=', 'auth-token')
                ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->count();

            $storageUsed = $this->getStorageUsage($school->id);

            $smsSent = DB::table('notifications')
                ->where('school_id', $school->id)
                ->where('type', 'sms')
                ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->count();

            $emailsSent = DB::table('notifications')
                ->where('school_id', $school->id)
                ->where('type', 'email')
                ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->count();

            TenantUsage::withoutGlobalScopes()->updateOrCreate(
                ['school_id' => $school->id, 'month' => $month],
                [
                    'active_students'    => $activeStudents,
                    'active_teachers'    => $activeTeachers,
                    'total_logins'       => $totalLogins,
                    'api_calls'          => $apiCalls,
                    'storage_used_bytes' => $storageUsed,
                    'sms_sent'           => $smsSent,
                    'emails_sent'        => $emailsSent,
                ]
            );

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Usage data collected for {$schools->count()} schools in {$month}.");

        return self::SUCCESS;
    }

    private function getStorageUsage(int $schoolId): int
    {
        $disk = \Illuminate\Support\Facades\Storage::disk('local');
        $path = "school_{$schoolId}";
        $totalBytes = 0;

        try {
            $directories = $disk->directories($path);
            foreach ($directories as $dir) {
                $files = $disk->allFiles($dir);
                foreach ($files as $file) {
                    $totalBytes += $disk->size($file);
                }
            }
        } catch (\Throwable) {
            return 0;
        }

        return $totalBytes;
    }
}
