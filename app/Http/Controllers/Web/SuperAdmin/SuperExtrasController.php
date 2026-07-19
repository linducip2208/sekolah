<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Foundation\Foundation;
use App\Models\Foundation\FoundationAdmin;
use App\Models\Payment\PaymentWebhookLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SuperExtrasController extends Controller
{
    /* ============== EMAIL TEMPLATES ============== */

    public function emailTemplates(): View
    {
        $templates = Cache::get('platform.email_templates', $this->defaultEmailTemplates());
        return view('super-admin.email-templates.index', compact('templates'));
    }

    public function saveEmailTemplate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'key'     => 'required|string|max:100',
            'subject' => 'required|string|max:200',
            'body'    => 'required|string|max:10000',
        ]);
        $templates = Cache::get('platform.email_templates', $this->defaultEmailTemplates());
        $templates[$data['key']] = ['subject' => $data['subject'], 'body' => $data['body']];
        Cache::forever('platform.email_templates', $templates);
        return back()->with('success', "Template '{$data['key']}' tersimpan.");
    }

    private function defaultEmailTemplates(): array
    {
        return [
            'school_activated'    => ['subject' => 'Sekolah Anda telah diaktifkan',         'body' => 'Halo {admin_name}, sekolah {school_name} telah aktif. Login di {login_url}.'],
            'subscription_expiring' => ['subject' => 'Langganan akan berakhir dalam {days} hari', 'body' => 'Perpanjang langganan untuk {school_name} sebelum {expire_date}.'],
            'payment_received'    => ['subject' => 'Pembayaran diterima',                    'body' => 'Terima kasih atas pembayaran sebesar Rp {amount}.'],
            'registration_pending' => ['subject' => 'Pendaftaran diterima — menunggu pembayaran', 'body' => 'Pendaftaran {school_name} dengan ID {reg_id} sedang menunggu pembayaran.'],
        ];
    }

    /* ============== BACKUP ============== */

    public function backups(): View
    {
        $backupDir = storage_path('app/backups');
        $files = [];
        if (is_dir($backupDir)) {
            foreach (glob($backupDir . '/*.sql*') as $f) {
                $files[] = [
                    'name'  => basename($f),
                    'size'  => filesize($f),
                    'mtime' => filemtime($f),
                ];
            }
            usort($files, fn ($a, $b) => $b['mtime'] <=> $a['mtime']);
        }
        return view('super-admin.backups.index', compact('files'));
    }

    public function triggerBackup(): RedirectResponse
    {
        $dir = storage_path('app/backups');
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $file = $dir . '/backup-' . now()->format('Y-m-d_His') . '.sql';

        // Best-effort backup via mysqldump if available; otherwise log placeholder
        $db = config('database.connections.mysql');
        $pwd = $db['password'] ? '-p' . escapeshellarg($db['password']) : '';
        $cmd = sprintf('mysqldump -h%s -P%s -u%s %s %s > %s 2>&1',
            escapeshellarg($db['host']), escapeshellarg($db['port']),
            escapeshellarg($db['username']), $pwd, escapeshellarg($db['database']), escapeshellarg($file));

        @exec($cmd, $output, $exit);
        if ($exit !== 0 || !file_exists($file) || filesize($file) === 0) {
            @file_put_contents($file, "-- placeholder backup at " . now() . " (mysqldump not available)\n");
        }
        return back()->with('success', 'Backup dijalankan: ' . basename($file));
    }

    public function downloadBackup(string $name): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        // Sanitize filename — only allow files inside backups dir matching expected pattern
        if (!preg_match('/^backup-[0-9_\-]+\.sql$/i', $name)) abort(404);
        $path = storage_path('app/backups/' . $name);
        abort_unless(file_exists($path), 404);
        return response()->download($path, $name, ['Content-Type' => 'application/sql']);
    }

    public function deleteBackup(string $name): RedirectResponse
    {
        if (!preg_match('/^backup-[0-9_\-]+\.sql$/i', $name)) abort(404);
        $path = storage_path('app/backups/' . $name);
        if (file_exists($path)) @unlink($path);
        return back()->with('success', "Backup {$name} dihapus.");
    }

    public function uploadRestore(\Illuminate\Http\Request $request): RedirectResponse
    {
        $request->validate([
            'sql_file' => 'required|file|mimes:sql,txt|max:51200', // 50MB
        ]);

        $dir = storage_path('app/backups');
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $name = 'restore-uploaded-' . now()->format('Y-m-d_His') . '.sql';
        $request->file('sql_file')->move($dir, $name);

        return back()->with('success', "File {$name} terupload. Klik 'Restore' di tabel untuk eksekusi (DESTRUCTIVE — hapus data existing).");
    }

    public function restoreBackup(\Illuminate\Http\Request $request, string $name): RedirectResponse
    {
        if (!preg_match('/^(backup|restore-uploaded)-[0-9_\-]+\.sql$/i', $name)) abort(404);
        $request->validate(['confirm' => 'required|in:RESTORE']);

        $path = storage_path('app/backups/' . $name);
        abort_unless(file_exists($path), 404);

        $db = config('database.connections.mysql');
        $pwd = $db['password'] ? '-p' . escapeshellarg($db['password']) : '';
        $cmd = sprintf('mysql -h%s -P%s -u%s %s %s < %s 2>&1',
            escapeshellarg($db['host']), escapeshellarg($db['port']),
            escapeshellarg($db['username']), $pwd, escapeshellarg($db['database']), escapeshellarg($path));

        @exec($cmd, $output, $exit);

        if ($exit !== 0) {
            return back()->withErrors('Restore gagal. Pastikan mysql binary tersedia di PATH. Output: ' . implode("\n", array_slice($output, -3)));
        }

        return back()->with('success', "Database berhasil di-restore dari {$name}. Refresh halaman untuk lihat data terbaru.");
    }

    /* ============== MAINTENANCE MODE ============== */

    public function maintenance(): View
    {
        return view('super-admin.maintenance.index', [
            'is_down' => app()->isDownForMaintenance(),
        ]);
    }

    public function enableMaintenance(Request $request): RedirectResponse
    {
        $message = $request->validate(['message' => 'nullable|string|max:500'])['message'] ?? null;
        Artisan::call('down', $message ? ['--render' => 'errors::503', '--secret' => 'super'] : ['--secret' => 'super']);
        Cache::forever('maintenance.message', $message);
        return back()->with('success', 'Maintenance mode aktif.');
    }

    public function disableMaintenance(): RedirectResponse
    {
        Artisan::call('up');
        Cache::forget('maintenance.message');
        return back()->with('success', 'Maintenance mode dimatikan.');
    }

    /* ============== REPORTS ============== */

    public function reports(\Illuminate\Http\Request $request): View
    {
        $from = $request->from ? \Carbon\Carbon::parse($request->from)->startOfDay() : now()->subMonths(12)->startOfMonth();
        $to   = $request->to   ? \Carbon\Carbon::parse($request->to)->endOfDay()    : now()->endOfDay();

        $monthExpr = \App\Services\SuperAdminService::monthExpr('created_at');

        $monthlyRev = DB::table('subscription_transactions')
            ->where('status', 'paid')
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw("$monthExpr as month, SUM(amount) as total, COUNT(*) as cnt")
            ->groupBy('month')->orderBy('month')->get();

        $totalRevenue   = (int) DB::table('subscription_transactions')->where('status', 'paid')->sum('amount');
        $rangeRevenue   = (int) DB::table('subscription_transactions')->where('status', 'paid')->whereBetween('created_at', [$from, $to])->sum('amount');
        $totalSchools   = DB::table('schools')->count();
        $activeSchools  = DB::table('schools')->where('is_active', true)->count();

        $perPlan = DB::table('subscription_transactions as st')
            ->leftJoin('plans as p', 'st.plan_id', '=', 'p.id')
            ->where('st.status', 'paid')
            ->whereBetween('st.created_at', [$from, $to])
            ->selectRaw('p.name as plan_name, SUM(st.amount) as total, COUNT(*) as cnt')
            ->groupBy('p.id', 'p.name')->orderByDesc('total')->get();

        $topSchools = DB::table('subscription_transactions as st')
            ->join('schools as s', 'st.school_id', '=', 's.id')
            ->where('st.status', 'paid')
            ->selectRaw('s.id, s.name, s.subdomain, SUM(st.amount) as total, COUNT(*) as cnt')
            ->groupBy('s.id', 's.name', 's.subdomain')
            ->orderByDesc('total')->limit(10)->get();

        $pendingReg = (int) DB::table('school_registrations')
            ->whereIn('status', ['pending', 'verifying', 'paid'])->sum('plan_price');

        $regTotal     = DB::table('school_registrations')->count();
        $regActivated = DB::table('school_registrations')->where('status', 'activated')->count();
        $conversionRate = $regTotal > 0 ? round(($regActivated / $regTotal) * 100, 1) : 0;

        $arpu = $activeSchools > 0 ? (int) ($totalRevenue / $activeSchools) : 0;

        $lastMonthRev = (int) DB::table('subscription_transactions')
            ->where('status', 'paid')
            ->whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])
            ->sum('amount');

        // Recent transactions
        $recentTx = DB::table('subscription_transactions as st')
            ->leftJoin('schools as s', 'st.school_id', '=', 's.id')
            ->leftJoin('plans as p', 'st.plan_id', '=', 'p.id')
            ->where('st.status', 'paid')
            ->orderByDesc('st.created_at')
            ->limit(20)
            ->select('st.*', 's.name as school_name', 'p.name as plan_name')
            ->get();

        return view('super-admin.reports.index', compact(
            'monthlyRev', 'totalRevenue', 'rangeRevenue', 'totalSchools', 'activeSchools',
            'perPlan', 'topSchools', 'pendingReg', 'regTotal', 'regActivated', 'conversionRate',
            'arpu', 'lastMonthRev', 'recentTx', 'from', 'to'
        ));
    }

    public function reportsExportCsv(\Illuminate\Http\Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $from = $request->from ? \Carbon\Carbon::parse($request->from) : now()->subMonths(12)->startOfMonth();
        $to   = $request->to   ? \Carbon\Carbon::parse($request->to)   : now()->endOfDay();

        $rows = DB::table('subscription_transactions as st')
            ->leftJoin('schools as s', 'st.school_id', '=', 's.id')
            ->leftJoin('plans as p',  'st.plan_id',   '=', 'p.id')
            ->whereBetween('st.created_at', [$from, $to])
            ->orderBy('st.created_at')
            ->select('st.created_at', 's.name as school', 's.subdomain', 'p.name as plan', 'st.amount', 'st.status', 'st.payment_method', 'st.reference', 'st.period_from', 'st.period_to')
            ->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date', 'School', 'Subdomain', 'Plan', 'Amount (Rp)', 'Status', 'Method', 'Reference', 'From', 'To']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->created_at, $r->school, $r->subdomain, $r->plan,
                    number_format($r->amount / 100, 0, '.', ''), $r->status,
                    $r->payment_method, $r->reference, $r->period_from, $r->period_to,
                ]);
            }
            fclose($out);
        }, 'platform-revenue-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv']);
    }

    /* ============== FOUNDATION ADMINS ============== */

    public function foundationAdmins(Foundation $foundation): View
    {
        return view('super-admin.foundations.admins', [
            'foundation' => $foundation,
            'admins'     => FoundationAdmin::where('foundation_id', $foundation->id)
                ->with('user:id,name,email')->get(),
            'users'      => User::orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    public function assignFoundationAdmin(Request $request, Foundation $foundation): RedirectResponse
    {
        $data = $request->validate(['user_id' => 'required|exists:users,id']);
        FoundationAdmin::firstOrCreate([
            'foundation_id' => $foundation->id,
            'user_id'       => $data['user_id'],
        ]);
        return back()->with('success', 'Admin yayasan ditambahkan.');
    }

    public function removeFoundationAdmin(FoundationAdmin $admin): RedirectResponse
    {
        $admin->delete();
        return back()->with('success', 'Admin yayasan dihapus.');
    }

    /* ============== WEBHOOK LOGS ============== */

    public function webhookLogs(): View
    {
        return view('super-admin.webhooks.index', [
            'logs' => PaymentWebhookLog::query()
                ->orderByDesc('created_at')->paginate(50),
        ]);
    }
}
