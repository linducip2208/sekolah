<?php

namespace App\Http\Controllers\Web\Admin\Communication;

use App\Http\Controllers\Controller;
use App\Models\Communication\BroadcastMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BroadcastMessageController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(): View
    {
        $schoolId = $this->schoolId();

        $messages = BroadcastMessage::where('school_id', $schoolId)
            ->with('creator:id,name')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('school-admin.communication.broadcast-messages', [
            'messages' => $messages,
        ]);
    }

    public function create(): View
    {
        return view('school-admin.communication.broadcast-create');
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'title'              => 'required|string|max:255',
            'message'            => 'required|string|max:5000',
            'channel'            => 'required|in:email,push,sms,whatsapp,all',
            'segment'            => 'required|in:all,students,parents,teachers,staff,custom',
            'custom_recipients'  => 'nullable|array',
            'custom_recipients.*'=> 'integer|exists:users,id',
            'scheduled_at'       => 'nullable|date|after:now',
        ]);

        $schoolId = $this->schoolId();

        $recipientCount = $this->resolveRecipientCount($schoolId, $data['segment'], $data['custom_recipients'] ?? null);

        $status = $data['scheduled_at'] ? 'scheduled' : 'draft';

        BroadcastMessage::create([
            'school_id'         => $schoolId,
            'title'             => $data['title'],
            'message'           => $data['message'],
            'channel'           => $data['channel'],
            'segment'           => $data['segment'],
            'custom_recipients' => $data['custom_recipients'] ?? null,
            'status'            => $status,
            'scheduled_at'      => $data['scheduled_at'] ?? null,
            'recipient_count'   => $recipientCount,
            'created_by'        => auth()->id(),
        ]);

        return redirect()->route('admin.broadcast.index')->with('success', 'Pesan broadcast berhasil dibuat.');
    }

    public function send(BroadcastMessage $message): \Illuminate\Http\RedirectResponse
    {
        if ($message->school_id !== $this->schoolId()) abort(403);

        $message->update([
            'status'  => 'sent',
            'sent_at' => now(),
        ]);

        return back()->with('success', 'Pesan broadcast berhasil dikirim.');
    }

    public function destroy(BroadcastMessage $message): \Illuminate\Http\RedirectResponse
    {
        if ($message->school_id !== $this->schoolId()) abort(403);

        $message->delete();

        return back()->with('success', 'Pesan broadcast berhasil dihapus.');
    }

    protected function resolveRecipientCount(int $schoolId, ?string $segment, ?array $customRecipients): int
    {
        if ($segment === 'custom' && $customRecipients) {
            return User::where('school_id', $schoolId)->whereIn('id', $customRecipients)->count();
        }

        $query = User::where('school_id', $schoolId);

        return match ($segment) {
            'students' => $query->whereHas('roles', fn ($q) => $q->where('name', 'student'))->count(),
            'parents'  => $query->whereHas('roles', fn ($q) => $q->where('name', 'parent'))->count(),
            'teachers' => $query->whereHas('roles', fn ($q) => $q->whereIn('name', ['teacher', 'homeroom_teacher']))->count(),
            'staff'    => $query->whereHas('roles', fn ($q) => $q->whereIn('name', ['admin', 'hr', 'accountant']))->count(),
            default    => $query->count(),
        };
    }
}
