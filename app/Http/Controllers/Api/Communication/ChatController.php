<?php

namespace App\Http\Controllers\Api\Communication;

use App\Http\Controllers\Controller;
use App\Models\Communication\Conversation;
use App\Models\Communication\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function conversations(): JsonResponse
    {
        $userId = auth()->id();
        $convs  = Conversation::where('school_id', auth()->user()->school_id)
            ->where(function ($q) use ($userId) {
                $q->where('user_one', $userId)->orWhere('user_two', $userId);
            })
            ->with('userOne', 'userTwo')
            ->latest('last_message_at')
            ->get();
        return response()->json($convs);
    }

    public function startConversation(Request $request): JsonResponse
    {
        $validated = $request->validate(['recipient_id' => 'required|integer|exists:users,id']);

        $me        = auth()->id();
        $recipient = $validated['recipient_id'];

        if ($recipient === $me) {
            return response()->json(['message' => 'Tidak dapat memulai percakapan dengan diri sendiri.'], 422);
        }

        $recipientUser = \App\Models\User::find($validated['recipient_id']);
        if (!$recipientUser || (int) $recipientUser->school_id !== (int) auth()->user()->school_id) {
            return response()->json(['message' => 'Pengguna tidak ditemukan.'], 404);
        }

        $userOne   = min($me, $recipient);
        $userTwo   = max($me, $recipient);

        $conv = Conversation::firstOrCreate(
            ['school_id' => auth()->user()->school_id, 'user_one' => $userOne, 'user_two' => $userTwo]
        );

        return response()->json($conv->load('userOne', 'userTwo'));
    }

    public function messages(Conversation $conversation): JsonResponse
    {
        $this->authorizeConversation($conversation);

        // Bounded history (latest 100, chronological). Use pagination
        // endpoint when full scrollback is needed.
        $messages = $conversation->messages()->with('sender')->latest()->limit(100)->get()->reverse()->values();

        Message::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', auth()->id())
            ->update(['is_read' => true]);

        return response()->json($messages);
    }

    public function send(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorizeConversation($conversation);

        $validated = $request->validate([
            'body' => 'required|string|max:5000',
            'file' => 'nullable|string|max:1000',
            'idempotency_key' => 'nullable|string|max:100',
        ]);

        $idempotencyKey = $validated['idempotency_key'] ?? $request->header('Idempotency-Key');

        // Idempotent retry: same key returns the original message (no duplicate).
        if ($idempotencyKey) {
            $existing = $conversation->messages()
                ->where('sender_id', auth()->id())
                ->where('idempotency_key', $idempotencyKey)
                ->first();
            if ($existing) {
                return response()->json($existing->load('sender'));
            }
        }

        $message = $conversation->messages()->create([
            'sender_id' => auth()->id(),
            'body'      => $validated['body'],
            'file'      => $validated['file'] ?? null,
            'idempotency_key' => $idempotencyKey,
        ]);

        $conversation->update(['last_message_at' => now()]);

        broadcast(new \App\Events\MessageSent($message))->toOthers();

        return response()->json($message->load('sender'), 201);
    }

    private function authorizeConversation(Conversation $conversation): void
    {
        $userId = auth()->id();
        abort_unless(
            (int) $conversation->school_id === (int) auth()->user()->school_id
                && ((int) $conversation->user_one === (int) $userId || (int) $conversation->user_two === (int) $userId),
            403,
            'Access denied.'
        );
    }
}
