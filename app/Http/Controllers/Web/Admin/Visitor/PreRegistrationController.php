<?php

namespace App\Http\Controllers\Web\Admin\Visitor;

use App\Http\Controllers\Controller;
use App\Models\Visitor\VisitorLog;
use App\Models\Visitor\VisitorQrSession;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PreRegistrationController extends Controller
{
    public function index(Request $request): View
    {
        $schoolId = auth()->user()->school_id;
        $tab = $request->get('tab', 'pending');

        $query = VisitorLog::where('school_id', $schoolId)
            ->where('pre_registered', true)
            ->with(['hostStaff.user:id,name', 'qrSessions']);

        match ($tab) {
            'pending'   => $query->where('status', 'pending'),
            'upcoming'  => $query->where('status', 'pending')
                ->where('expected_arrival', '>=', Carbon::today())
                ->orderBy('expected_arrival'),
            'today'     => $query->where(function ($q) {
                $q->where('status', 'checked_in')
                  ->orWhere('checked_in_at', '>=', Carbon::today());
            }),
            'history'   => $query->whereIn('status', ['checked_out', 'cancelled'])
                ->orderByDesc('checked_out_at'),
            'cancelled' => $query->where('status', 'cancelled'),
            default     => $query->where('status', 'pending'),
        };

        $visitors = $query->orderByDesc('created_at')->paginate(30)->withQueryString();

        return view('school-admin.visitor.pre-registration', compact('visitors', 'tab'));
    }

    public function checkIn(VisitorLog $visitor): RedirectResponse
    {
        $visitor->update([
            'status'        => 'checked_in',
            'checked_in_at' => now(),
        ]);

        return back()->with('success', "{$visitor->visitor_name} berhasil check-in.");
    }

    public function checkOut(VisitorLog $visitor): RedirectResponse
    {
        $visitor->update([
            'status'         => 'checked_out',
            'checked_out_at' => now(),
        ]);

        return back()->with('success', "{$visitor->visitor_name} berhasil check-out.");
    }

    public function cancel(VisitorLog $visitor): RedirectResponse
    {
        $visitor->update(['status' => 'cancelled']);
        return back()->with('success', "Kunjungan {$visitor->visitor_name} dibatalkan.");
    }

    public function export(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\RedirectResponse
    {
        $schoolId = auth()->user()->school_id;

        $visitors = VisitorLog::where('school_id', $schoolId)
            ->where('pre_registered', true)
            ->with('hostStaff.user:id,name')
            ->orderByDesc('created_at')
            ->get();

        $filename = 'visitor-pre-registration-' . date('Y-m-d') . '.csv';
        $handle = fopen('php://temp', 'r+');

        fputcsv($handle, ['Nama Tamu', 'No HP', 'Tujuan', 'Host', 'Kendaraan', 'Kedatangan', 'Status', 'Check-in', 'Check-out', 'QR']);

        foreach ($visitors as $v) {
            fputcsv($handle, [
                $v->visitor_name,
                $v->phone,
                $v->purpose,
                $v->hostStaff?->user?->name ?? '-',
                $v->vehicle_plate ?? '-',
                $v->expected_arrival?->format('d M Y H:i') ?? '-',
                $v->status,
                $v->checked_in_at?->format('d M Y H:i') ?? '-',
                $v->checked_out_at?->format('d M Y H:i') ?? '-',
                $v->qr_code ?? '-',
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        $path = storage_path("app/temp/{$filename}");
        file_put_contents($path, $csv);

        return response()->download($path, $filename, ['Content-Type' => 'text/csv'])->deleteFileAfterSend();
    }
}
