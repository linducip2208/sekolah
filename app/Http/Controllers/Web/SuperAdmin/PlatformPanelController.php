<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Foundation\Foundation;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class PlatformPanelController extends Controller
{
    /**
     * Lintas-tenant user list with search + role/school filter.
     */
    public function users(Request $request): View
    {
        $users = User::query()
            ->with(['school:id,name,subdomain', 'roles:id,name'])
            ->when($request->search, fn ($q) => $q
                ->where(fn ($s) => $s->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%")))
            ->when($request->school_id, fn ($q) => $q->where('school_id', $request->school_id))
            ->when($request->role, fn ($q) => $q->whereHas('roles', fn ($r) => $r->where('name', $request->role)))
            ->when($request->status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($request->status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        return view('super-admin.users.index', [
            'users'   => $users,
            'schools' => School::withoutGlobalScopes()->orderBy('name')->get(['id', 'name']),
            'roles'   => DB::table('roles')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function toggleUser(User $user): RedirectResponse
    {
        $user->update(['is_active' => !$user->is_active]);
        return back()->with('success',
            "User {$user->email} " . ($user->is_active ? 'diaktifkan' : 'dinonaktifkan'));
    }

    /**
     * Audit log — read from Spatie activity_log.
     */
    public function auditLog(Request $request): View
    {
        $logs = Activity::query()
            ->with(['causer:id,name,email', 'subject'])
            ->when($request->event, fn ($q) => $q->where('event', $request->event))
            ->when($request->log_name, fn ($q) => $q->where('log_name', $request->log_name))
            ->when($request->causer_id, fn ($q) => $q->where('causer_id', $request->causer_id))
            ->orderByDesc('created_at')
            ->paginate(50)
            ->withQueryString();

        return view('super-admin.audit.index', [
            'logs'   => $logs,
            'events' => Activity::query()->whereNotNull('event')->distinct()->pluck('event'),
        ]);
    }

    /**
     * Foundations (yayasan) — index + simple CRUD.
     */
    public function foundations(Request $request): View
    {
        $foundations = Foundation::query()
            ->with(['admins:id,foundation_id,user_id'])
            ->withCount('admins')
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('super-admin.foundations.index', compact('foundations'));
    }

    public function createFoundation(): View
    {
        return view('super-admin.foundations.create');
    }

    public function storeFoundation(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'    => 'required|string|max:255',
            'slug'    => 'required|string|alpha_dash|max:255|unique:foundations,slug',
            'address' => 'nullable|string',
            'npwp'    => 'nullable|string|max:30',
        ]);
        $data['is_active'] = true;
        Foundation::create($data);

        return redirect()->route('super.foundations.index')
            ->with('success', 'Yayasan berhasil dibuat.');
    }

    /**
     * Announcements — broadcast pengumuman ke semua admin sekolah.
     * Stored in cache as a feed; admins lihat di dashboard mereka nanti.
     */
    public function announcements(): View
    {
        $announcements = Cache::get('platform.announcements', []);
        return view('super-admin.announcements.index', compact('announcements'));
    }

    public function storeAnnouncement(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'   => 'required|string|max:255',
            'body'    => 'required|string|max:5000',
            'level'   => 'required|in:info,warning,critical',
        ]);

        $list = Cache::get('platform.announcements', []);
        array_unshift($list, [
            'id'         => (string) \Illuminate\Support\Str::uuid(),
            'title'      => $data['title'],
            'body'       => $data['body'],
            'level'      => $data['level'],
            'created_at' => now()->toIso8601String(),
            'created_by' => auth()->user()->name,
        ]);
        Cache::forever('platform.announcements', array_slice($list, 0, 50));

        return back()->with('success', 'Pengumuman terkirim ke semua sekolah.');
    }

    public function deleteAnnouncement(string $id): RedirectResponse
    {
        $list = Cache::get('platform.announcements', []);
        $list = array_values(array_filter($list, fn ($a) => ($a['id'] ?? null) !== $id));
        Cache::forever('platform.announcements', $list);
        return back()->with('success', 'Pengumuman dihapus.');
    }

    /**
     * System Health — DB, cache, queue, storage.
     */
    public function systemHealth(): View
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache'    => $this->checkCache(),
            'storage'  => $this->checkStorage(),
            'queue'    => $this->checkQueue(),
            'mail'     => [
                'status'  => 'info',
                'driver'  => config('mail.default'),
                'message' => 'Driver: ' . config('mail.default'),
            ],
        ];

        $serverInfo = [
            'php_version'      => PHP_VERSION,
            'laravel_version'  => app()->version(),
            'environment'      => app()->environment(),
            'timezone'         => config('app.timezone'),
            'debug_mode'       => config('app.debug') ? 'ON' : 'OFF',
            'maintenance_mode' => app()->isDownForMaintenance() ? 'ON' : 'OFF',
        ];

        $dbStats = [
            'tables'  => count(DB::select('SHOW TABLES')),
            'schools' => School::withoutGlobalScopes()->count(),
            'users'   => User::count(),
        ];

        return view('super-admin.system.health', compact('checks', 'serverInfo', 'dbStats'));
    }

    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            return ['status' => 'ok', 'message' => 'Connected: ' . DB::connection()->getDriverName()];
        } catch (\Throwable $e) {
            return ['status' => 'fail', 'message' => $e->getMessage()];
        }
    }

    private function checkCache(): array
    {
        try {
            Cache::put('__health_test', 'ok', 5);
            $val = Cache::get('__health_test');
            return ['status' => $val === 'ok' ? 'ok' : 'fail', 'message' => 'Driver: ' . config('cache.default')];
        } catch (\Throwable $e) {
            return ['status' => 'fail', 'message' => $e->getMessage()];
        }
    }

    private function checkStorage(): array
    {
        try {
            $path = storage_path('app');
            $free = disk_free_space($path);
            $total = disk_total_space($path);
            return [
                'status'  => is_writable($path) ? 'ok' : 'fail',
                'message' => sprintf('Free: %s / %s', $this->humanBytes($free), $this->humanBytes($total)),
            ];
        } catch (\Throwable $e) {
            return ['status' => 'fail', 'message' => $e->getMessage()];
        }
    }

    private function checkQueue(): array
    {
        $driver = config('queue.default');
        $pending = 0;
        try {
            if ($driver === 'database') {
                $pending = DB::table('jobs')->count();
            }
            return ['status' => 'ok', 'message' => "Driver: {$driver} · Pending: {$pending}"];
        } catch (\Throwable $e) {
            return ['status' => 'warn', 'message' => $e->getMessage()];
        }
    }

    private function humanBytes(float $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024; $i++;
        }
        return sprintf('%.1f %s', $bytes, $units[$i]);
    }
}
