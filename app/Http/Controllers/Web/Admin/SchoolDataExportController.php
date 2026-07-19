<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ExportSchoolDataJob;
use App\Models\SchoolDataExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SchoolDataExportController extends Controller
{
    public function index()
    {
        $schoolId = auth()->user()->school_id;
        $exports = SchoolDataExport::where('school_id', $schoolId)
            ->with('requester:id,name')
            ->orderByDesc('id')
            ->paginate(25);
        return view('school-admin.exports.index', compact('exports'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'scope'    => 'nullable|in:all,academic,finance,communication,custom',
            'tables'   => 'nullable|array',
            'tables.*' => 'string|max:64|regex:/^[a-z0-9_]+$/',
        ]);

        $schoolId = auth()->user()->school_id;
        $userId   = auth()->id();

        $active = SchoolDataExport::where('school_id', $schoolId)
            ->whereIn('status', ['queued', 'processing'])
            ->exists();
        if ($active) {
            return back()->withErrors(['export' => 'Sudah ada export sedang berjalan. Tunggu hingga selesai.']);
        }

        $scope = $data['scope'] ?? 'all';
        $tablesFilter = null;
        if ($scope === 'custom' && !empty($data['tables'])) {
            $tablesFilter = array_values(array_unique($data['tables']));
        } elseif ($scope !== 'all') {
            $tablesFilter = self::scopeTablePatterns($scope);
        }

        $export = SchoolDataExport::create([
            'school_id'    => $schoolId,
            'requested_by' => $userId,
            'format'       => 'zip',
            'status'       => 'queued',
            'included_tables' => $tablesFilter,
        ]);

        ExportSchoolDataJob::dispatch($export->id);

        return back()->with('success', 'Export antri. Anda dapat refresh untuk melihat status.');
    }

    public static function scopeTablePatterns(string $scope): array
    {
        return match ($scope) {
            'academic' => [
                'students', 'staffs', 'attendances', 'exams', 'exam_results',
                'marks', 'report_cards', 'class_sections', 'class_rooms',
                'sections', 'subjects', 'academic_years', 'semesters',
                'timetables', 'timetable_slots', 'assignments', 'assignment_submissions',
                'lessons',
            ],
            'finance' => [
                'fee_structures', 'fee_invoices', 'fee_payments',
                'payroll_structures', 'salary_slips', 'subscription_transactions',
                'school_payment_providers', 'school_payment_methods',
            ],
            'communication' => [
                'notices', 'conversations', 'messages', 'notifications_log',
                'notification_providers', 'webhooks', 'webhook_deliveries',
            ],
            default => [],
        };
    }

    public function download(SchoolDataExport $export)
    {
        abort_unless($export->school_id === auth()->user()->school_id, 403);
        abort_unless($export->isReady(), 410, 'Export belum siap atau sudah expired.');

        return Storage::disk('local')->download($export->file_path,
            'eschool-export-' . $export->school_id . '-' . $export->id . '.zip');
    }

    public function destroy(SchoolDataExport $export)
    {
        abort_unless($export->school_id === auth()->user()->school_id, 403);
        if ($export->file_path && Storage::disk('local')->exists($export->file_path)) {
            Storage::disk('local')->delete($export->file_path);
        }
        $export->delete();
        return back()->with('success', 'Export dihapus.');
    }
}
