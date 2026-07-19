<?php

namespace App\Http\Controllers\Web\Admin\Bulk;

use App\Http\Controllers\Controller;
use App\Jobs\SendWhatsAppNotification;
use App\Models\Academic\Staff;
use App\Models\Academic\Student;
use App\Models\Communication\Notice;
use App\Models\Finance\FeeInvoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BulkActionController extends Controller
{
    private function schoolId(): int { return auth()->user()->school_id; }

    public function studentsBulk(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'action' => 'required|in:delete,activate,deactivate,assign_class,send_whatsapp',
            'ids'    => 'required|array|min:1',
            'ids.*'  => 'integer|exists:students,id',
            'class_section_id' => 'nullable|exists:class_sections,id',
            'whatsapp_message' => 'nullable|string|max:2000',
        ]);

        $students = Student::where('school_id', $this->schoolId())->whereIn('id', $data['ids'])->with('user')->get();
        $count = 0;

        if ($data['action'] === 'send_whatsapp') {
            $message = $data['whatsapp_message'] ?? 'Pesan dari sekolah.';
            foreach ($students as $s) {
                $phone = $s->whatsapp_phone ?? $s->guardian_phone ?? $s->user?->phone;
                if ($phone) {
                    SendWhatsAppNotification::dispatch($phone, $message, $this->schoolId());
                    $count++;
                }
            }
            return back()->with('success', "WhatsApp dikirim ke {$count} nomor.");
        }

        DB::transaction(function () use ($students, $data, &$count) {
            foreach ($students as $s) {
                match ($data['action']) {
                    'delete'       => $s->delete() && $s->user?->update(['is_active' => false]),
                    'activate'     => $s->user?->update(['is_active' => true]),
                    'deactivate'   => $s->user?->update(['is_active' => false]),
                    'assign_class' => $s->update(['class_section_id' => $data['class_section_id']]),
                };
                $count++;
            }
        });

        return back()->with('success', "{$count} siswa di-{$data['action']}.");
    }

    public function staffBulk(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'action' => 'required|in:delete,activate,deactivate,send_whatsapp',
            'ids'    => 'required|array|min:1',
            'ids.*'  => 'integer|exists:staffs,id',
            'whatsapp_message' => 'nullable|string|max:2000',
        ]);

        $staffs = Staff::where('school_id', $this->schoolId())->whereIn('id', $data['ids'])->with('user')->get();
        $count = 0;

        if ($data['action'] === 'send_whatsapp') {
            $message = $data['whatsapp_message'] ?? 'Pesan dari sekolah.';
            foreach ($staffs as $st) {
                $phone = $st->whatsapp_phone ?? $st->user?->phone;
                if ($phone) {
                    SendWhatsAppNotification::dispatch($phone, $message, $this->schoolId());
                    $count++;
                }
            }
            return back()->with('success', "WhatsApp dikirim ke {$count} staf.");
        }

        DB::transaction(function () use ($staffs, $data, &$count) {
            foreach ($staffs as $st) {
                match ($data['action']) {
                    'delete'     => $st->delete() && $st->user?->update(['is_active' => false]),
                    'activate'   => $st->user?->update(['is_active' => true]),
                    'deactivate' => $st->user?->update(['is_active' => false]),
                };
                $count++;
            }
        });

        return back()->with('success', "{$count} staff di-{$data['action']}.");
    }

    public function invoicesBulk(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'action' => 'required|in:delete,mark_overdue',
            'ids'    => 'required|array|min:1',
            'ids.*'  => 'integer|exists:fee_invoices,id',
        ]);

        $invoices = FeeInvoice::where('school_id', $this->schoolId())->whereIn('id', $data['ids'])->get();
        $count = 0; $skipped = 0;

        foreach ($invoices as $i) {
            if ($data['action'] === 'delete') {
                if ($i->paid_amount > 0) { $skipped++; continue; }
                $i->delete();
            } elseif ($data['action'] === 'mark_overdue') {
                $i->update(['status' => 'overdue']);
            }
            $count++;
        }

        $msg = "{$count} invoice di-{$data['action']}.";
        if ($skipped > 0) $msg .= " {$skipped} skip (sudah ada pembayaran).";
        return back()->with('success', $msg);
    }

    public function noticesBulk(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'action' => 'required|in:delete,publish,unpublish',
            'ids'    => 'required|array|min:1',
            'ids.*'  => 'integer|exists:notices,id',
        ]);

        $notices = Notice::where('school_id', $this->schoolId())->whereIn('id', $data['ids'])->get();
        $count = 0;

        foreach ($notices as $n) {
            match ($data['action']) {
                'delete'    => $n->delete(),
                'publish'   => $n->update(['is_published' => true]),
                'unpublish' => $n->update(['is_published' => false]),
            };
            $count++;
        }

        return back()->with('success', "{$count} pengumuman di-{$data['action']}.");
    }
}
