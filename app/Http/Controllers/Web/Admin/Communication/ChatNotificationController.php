<?php

namespace App\Http\Controllers\Web\Admin\Communication;

use App\Http\Controllers\Controller;
use App\Models\Communication\Conversation;
use App\Models\Communication\Message;
use App\Models\Communication\NotificationLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ChatNotificationController extends Controller
{
    private function schoolId(): int { return auth()->user()->school_id; }

    public function inbox(): View
    {
        $userId = auth()->id();
        $conversations = Conversation::where('school_id', $this->schoolId())
            ->where(fn($q) => $q->where('user_one', $userId)->orWhere('user_two', $userId))
            ->with(['userOne:id,name', 'userTwo:id,name'])
            ->orderByDesc('last_message_at')->paginate(20);

        return view('school-admin.chat.inbox', [
            'conversations' => $conversations,
            'users'         => User::where('school_id', $this->schoolId())
                ->where('id', '!=', $userId)->where('is_active', true)
                ->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function showConversation(Conversation $conversation): View
    {
        abort_unless($conversation->school_id === $this->schoolId(), 403);
        $userId = auth()->id();
        abort_unless($conversation->user_one === $userId || $conversation->user_two === $userId, 403);

        return view('school-admin.chat.show', [
            'conversation' => $conversation->load(['userOne:id,name', 'userTwo:id,name']),
            'messages'     => Message::where('conversation_id', $conversation->id)
                ->with('sender:id,name')->orderBy('created_at')->get(),
        ]);
    }

    public function startConversation(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id|different:'.auth()->id(),
            'message' => 'required|string|max:5000',
        ]);
        $userId = auth()->id();

        $conversation = DB::transaction(function () use ($data, $userId) {
            $existing = Conversation::where('school_id', $this->schoolId())
                ->where(fn($q) => $q
                    ->where(fn($s) => $s->where('user_one', $userId)->where('user_two', $data['user_id']))
                    ->orWhere(fn($s) => $s->where('user_one', $data['user_id'])->where('user_two', $userId)))
                ->first();

            $conv = $existing ?? Conversation::create([
                'school_id' => $this->schoolId(),
                'user_one'  => $userId,
                'user_two'  => $data['user_id'],
            ]);

            Message::create([
                'conversation_id' => $conv->id,
                'sender_id'       => $userId,
                'body'            => $data['message'],
                'is_read'         => false,
            ]);
            $conv->update(['last_message_at' => now()]);
            return $conv;
        });

        return redirect()->route('admin.chat.show', $conversation);
    }

    public function sendMessage(Request $request, Conversation $conversation): RedirectResponse
    {
        abort_unless($conversation->school_id === $this->schoolId(), 403);
        $data = $request->validate(['body' => 'required|string|max:5000']);
        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => auth()->id(),
            'body'            => $data['body'],
            'is_read'         => false,
        ]);
        $conversation->update(['last_message_at' => now()]);
        return back();
    }

    public function notifications(): View
    {
        return view('school-admin.notifications.index', [
            'notifications' => NotificationLog::where('school_id', $this->schoolId())
                ->where('user_id', auth()->id())
                ->orderByDesc('created_at')->paginate(30),
        ]);
    }

    public function markRead(NotificationLog $notification): RedirectResponse
    {
        abort_unless($notification->school_id === $this->schoolId() && $notification->user_id === auth()->id(), 403);
        $notification->update(['is_read' => true]);
        return back();
    }
}
