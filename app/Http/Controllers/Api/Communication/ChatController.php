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
        $convs  = Conversation::where('user_one', $userId)
            ->orWhere('user_two', $userId)
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
        $userOne   = min($me, $recipient);
        $userTwo   = max($me, $recipient);

        $conv = Conversation::firstOrCreate(
            ['school_id' => auth()->user()->school_id, 'user_one' => $userOne, 'user_two' => $userTwo]
        );

        return response()->json($conv->load('userOne', 'userTwo'));
    }

    public function messages(Conversation $conversation): JsonResponse
    {
        $messages = $conversation->messages()->with('sender')->orderBy('created_at')->get();

        Message::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', auth()->id())
            ->update(['is_read' => true]);

        return response()->json($messages);
    }

    public function send(Request $request, Conversation $conversation): JsonResponse
    {
        $validated = $request->validate([
            'body' => 'required|string|max:5000',
            'file' => 'nullable|string|max:1000',
        ]);

        $message = $conversation->messages()->create([
            'sender_id' => auth()->id(),
            'body'      => $validated['body'],
            'file'      => $validated['file'] ?? null,
        ]);

        $conversation->update(['last_message_at' => now()]);

        return response()->json($message->load('sender'), 201);
    }
}
