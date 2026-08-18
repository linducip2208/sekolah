<?php

namespace App\Http\Controllers\Web\Admin\Office;

use App\Http\Controllers\Controller;
use App\Models\AdminOffice\IncomingMail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IncomingMailController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(Request $request): View
    {
        $mails = IncomingMail::where('school_id', $this->schoolId())
            ->with('dispositionUser:id,name')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderByDesc('received_date')
            ->paginate(25)
            ->withQueryString();

        return view('school-admin.office.incoming-mails', [
            'mails'  => $mails,
            'staff'  => User::where('school_id', $this->schoolId())->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'mail_no'        => 'required|string|max:50',
            'sender_name'    => 'required|string|max:200',
            'sender_address' => 'nullable|string',
            'subject'        => 'required|string|max:300',
            'received_date'  => 'required|date',
            'document'       => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png',
        ]);

        $path = null;
        if ($request->hasFile('document')) {
            $path = $request->file('document')->store('mails/incoming', 'public');
        }

        IncomingMail::create([
            'school_id'      => $this->schoolId(),
            'mail_no'        => $data['mail_no'],
            'sender_name'    => $data['sender_name'],
            'sender_address' => $data['sender_address'] ?? null,
            'subject'        => $data['subject'],
            'received_date'  => $data['received_date'],
            'status'         => 'received',
            'document_path'  => $path,
        ]);

        return back()->with('success', 'Surat masuk dicatat.');
    }

    public function disposition(Request $request, IncomingMail $mail): RedirectResponse
    {
        abort_unless($mail->school_id === $this->schoolId(), 403);

        $data = $request->validate([
            'disposition_to'   => 'required|exists:users,id',
            'disposition_notes'=> 'nullable|string',
        ]);

        $mail->update([
            'disposition_to'    => $data['disposition_to'],
            'disposition_notes' => $data['disposition_notes'] ?? null,
            'status'            => 'dispositioned',
        ]);

        return back()->with('success', 'Disposisi surat dicatat.');
    }

    public function archive(IncomingMail $mail): RedirectResponse
    {
        abort_unless($mail->school_id === $this->schoolId(), 403);
        $mail->update(['status' => 'archived']);
        return back()->with('success', 'Surat diarsipkan.');
    }

    public function destroy(IncomingMail $mail): RedirectResponse
    {
        abort_unless($mail->school_id === $this->schoolId(), 403);
        $mail->delete();
        return back()->with('success', 'Surat masuk dihapus.');
    }
}
