<?php

namespace App\Http\Controllers\Web\Admin\Office;

use App\Http\Controllers\Controller;
use App\Models\AdminOffice\OutgoingMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OutgoingMailController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(Request $request): View
    {
        $mails = OutgoingMail::where('school_id', $this->schoolId())
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderByDesc('sent_date')
            ->paginate(25)
            ->withQueryString();

        return view('school-admin.office.outgoing-mails', [
            'mails' => $mails,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'mail_no'         => 'required|string|max:50',
            'recipient_name'  => 'required|string|max:200',
            'recipient_address'=> 'nullable|string',
            'subject'         => 'required|string|max:300',
            'sent_date'       => 'required|date',
            'document'        => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png',
        ]);

        $path = null;
        if ($request->hasFile('document')) {
            $path = $request->file('document')->store('mails/outgoing', 'public');
        }

        OutgoingMail::create([
            'school_id'       => $this->schoolId(),
            'mail_no'         => $data['mail_no'],
            'recipient_name'  => $data['recipient_name'],
            'recipient_address'=> $data['recipient_address'] ?? null,
            'subject'         => $data['subject'],
            'sent_date'       => $data['sent_date'],
            'status'          => 'draft',
            'document_path'   => $path,
        ]);

        return back()->with('success', 'Surat keluar dicatat.');
    }

    public function markSent(OutgoingMail $mail): RedirectResponse
    {
        abort_unless($mail->school_id === $this->schoolId(), 403);
        $mail->update(['status' => 'sent']);
        return back()->with('success', 'Surat ditandai terkirim.');
    }

    public function archive(OutgoingMail $mail): RedirectResponse
    {
        abort_unless($mail->school_id === $this->schoolId(), 403);
        $mail->update(['status' => 'archived']);
        return back()->with('success', 'Surat diarsipkan.');
    }

    public function destroy(OutgoingMail $mail): RedirectResponse
    {
        abort_unless($mail->school_id === $this->schoolId(), 403);
        $mail->delete();
        return back()->with('success', 'Surat keluar dihapus.');
    }
}
