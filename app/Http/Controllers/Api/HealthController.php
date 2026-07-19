<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class HealthController extends Controller
{
    public function shallow(): JsonResponse
    {
        return response()->json(['status' => 'ok', 'time' => now()->toIso8601String()]);
    }

    public function deep(): JsonResponse
    {
        $checks = [
            'database'   => $this->checkDatabase(),
            'cache'      => $this->checkCache(),
            'storage'    => $this->checkStorage(),
            'queue'      => $this->checkQueue(),
            'app_key'    => !empty(config('app.key')),
        ];

        $allOk = !in_array(false, array_map(fn ($v) => is_array($v) ? $v['ok'] : $v, $checks), true);

        return response()->json([
            'status'  => $allOk ? 'ok' : 'degraded',
            'checks'  => $checks,
            'time'    => now()->toIso8601String(),
            'version' => config('app.version', '1.0.0'),
        ], $allOk ? 200 : 503);
    }

    public function metrics(): JsonResponse
    {
        return response()->json([
            'schools_active'        => DB::table('schools')->where('is_active', true)->count(),
            'users_total'           => DB::table('users')->count(),
            'students_total'        => DB::table('students')->count(),
            'payments_pending'      => DB::table('payment_transactions')
                ->where('status', 'awaiting_payment')->count(),
            'payments_paid_today'   => DB::table('payment_transactions')
                ->where('status', 'paid')
                ->whereDate('paid_at', today())->count(),
            'donations_completed_today' => DB::table('donations')
                ->where('status', 'completed')
                ->whereDate('donated_at', today())->count(),
            'queue_pending'         => $this->jobsCount(),
            'failed_jobs'           => DB::table('failed_jobs')->count(),
            'time'                  => now()->toIso8601String(),
        ]);
    }

    protected function checkDatabase(): array
    {
        try {
            $start = microtime(true);
            DB::select('SELECT 1');
            return ['ok' => true, 'latency_ms' => (int) ((microtime(true) - $start) * 1000)];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    protected function checkCache(): array
    {
        try {
            $key = '_health_' . uniqid();
            Cache::put($key, '1', 5);
            $value = Cache::get($key);
            Cache::forget($key);
            return ['ok' => $value === '1'];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    protected function checkStorage(): array
    {
        try {
            $disk = config('filesystems.default');
            Storage::disk($disk)->exists('/');
            return ['ok' => true, 'disk' => $disk];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    protected function checkQueue(): array
    {
        try {
            return ['ok' => true, 'pending' => $this->jobsCount()];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    protected function jobsCount(): int
    {
        try {
            return (int) DB::table('jobs')->count();
        } catch (\Throwable) {
            return 0;
        }
    }
}
