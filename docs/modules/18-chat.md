# Module 18 — One-to-One Chat

## Depends On
Module 02 (auth — all roles must exist)

## What to Build
Chat 1-on-1 antar pengguna dalam satu sekolah. Real-time menggunakan
Laravel Broadcasting + WebSocket (Pusher/Soketi). Kirim teks + lampiran file/gambar.
Semua role dapat chat, dengan batasan: tidak bisa chat lintas sekolah.

---

## Database Schema

```php
// conversations table
Schema::create('conversations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_one')->constrained('users')->cascadeOnDelete();
    $table->foreignId('user_two')->constrained('users')->cascadeOnDelete();
    $table->foreignId('last_message_id')->nullable(); // denormalized untuk performa
    $table->timestamp('last_message_at')->nullable();
    $table->timestamps();
    $table->unique(['school_id', 'user_one', 'user_two']);
    $table->index(['school_id', 'user_one', 'last_message_at']);
    $table->index(['school_id', 'user_two', 'last_message_at']);
});

// messages table
Schema::create('messages', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
    $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
    $table->text('message')->nullable();
    $table->string('attachment')->nullable();           // S3 path
    $table->string('attachment_type')->nullable();      // image | file | audio
    $table->string('attachment_name')->nullable();      // nama file asli
    $table->enum('type', ['text', 'image', 'file', 'audio'])->default('text');
    $table->boolean('is_read')->default(false);
    $table->timestamp('read_at')->nullable();
    $table->timestamps();
    $table->softDeletes();
    $table->index(['school_id', 'conversation_id', 'created_at']);
    $table->index(['school_id', 'sender_id']);
});
```

---

## API Endpoints

| Method | URI                                                    | Role | Deskripsi                              |
|--------|--------------------------------------------------------|------|----------------------------------------|
| GET    | `/api/v1/chat/conversations`                           | all  | List percakapan (sorted by last msg)   |
| POST   | `/api/v1/chat/conversations`                           | all  | Mulai percakapan baru                  |
| GET    | `/api/v1/chat/conversations/{id}`                      | all  | Detail percakapan + info user          |
| GET    | `/api/v1/chat/conversations/{id}/messages`             | all  | List pesan (paginated, cursor-based)   |
| POST   | `/api/v1/chat/conversations/{id}/messages`             | all  | Kirim pesan teks                       |
| POST   | `/api/v1/chat/conversations/{id}/messages/attachment`  | all  | Kirim pesan dengan file/gambar         |
| POST   | `/api/v1/chat/conversations/{id}/read`                 | all  | Tandai semua pesan sudah dibaca        |
| GET    | `/api/v1/chat/users`                                   | all  | Search user yang bisa diajak chat      |
| GET    | `/api/v1/chat/unread-count`                            | all  | Jumlah pesan belum dibaca              |

---

## Broadcasting Event

```php
// app/Events/MessageSent.php
class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Message $message) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("conversation.{$this->message->conversation_id}"),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'id'              => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'sender'          => [
                'id'     => $this->message->sender->id,
                'name'   => $this->message->sender->name,
                'avatar' => $this->message->sender->avatar_url,
            ],
            'message'         => $this->message->message,
            'type'            => $this->message->type,
            'attachment_url'  => $this->message->attachment_url,
            'created_at'      => $this->message->created_at->toIso8601String(),
        ];
    }
}

// routes/channels.php
Broadcast::channel('conversation.{conversationId}', function (User $user, int $conversationId) {
    $conversation = Conversation::find($conversationId);
    return $conversation &&
        ($conversation->user_one === $user->id || $conversation->user_two === $user->id);
});
```

---

## ChatService Implementation

```php
// Modules/Communication/Services/ChatService.php
class ChatService
{
    public function getOrCreateConversation(int $userId, int $targetUserId): Conversation
    {
        $schoolId = auth()->user()->school_id;

        // Normalize: user_one selalu ID lebih kecil
        [$u1, $u2] = $userId < $targetUserId
            ? [$userId, $targetUserId]
            : [$targetUserId, $userId];

        return Conversation::firstOrCreate(
            ['school_id' => $schoolId, 'user_one' => $u1, 'user_two' => $u2],
        );
    }

    public function sendMessage(Conversation $conversation, User $sender, array $data): Message
    {
        $message = Message::create([
            'school_id'       => $sender->school_id,
            'conversation_id' => $conversation->id,
            'sender_id'       => $sender->id,
            'message'         => $data['message'] ?? null,
            'type'            => $data['type'] ?? 'text',
            'attachment'      => $data['attachment'] ?? null,
            'attachment_type' => $data['attachment_type'] ?? null,
            'attachment_name' => $data['attachment_name'] ?? null,
        ]);

        $conversation->update([
            'last_message_id' => $message->id,
            'last_message_at' => now(),
        ]);

        broadcast(new MessageSent($message))->toOthers();

        // FCM ke penerima jika offline
        $receiverId = $conversation->user_one === $sender->id
            ? $conversation->user_two
            : $conversation->user_one;

        NotifyChatMessageJob::dispatch($message, $receiverId);

        return $message->load('sender');
    }

    public function markAsRead(Conversation $conversation, User $reader): void
    {
        Message::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', $reader->id)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);
    }
}
```

---

## Flutter Chat Implementation

```dart
// lib/features/chat/presentation/pages/chat_page.dart

class ChatPage extends StatefulWidget {
  final int conversationId;
  final UserModel otherUser;
}

// Gunakan Pusher Flutter SDK untuk subscribe ke channel
// channel: 'private-conversation.{conversationId}'
// event: 'MessageSent'
//
// UI:
//   - ListView.builder (messages, reversed)
//   - Bubble: biru (sent) | abu (received)
//   - Timestamp + read indicator (✓ / ✓✓)
//   - Input bar: TextField + camera icon + send button
//   - Image preview sebelum kirim
```

---

## Acceptance Criteria

- [ ] Conversation antara dua user hanya ada satu (unique)
- [ ] User hanya bisa lihat conversation miliknya
- [ ] Pesan real-time via WebSocket (broadcast)
- [ ] FCM dikirim jika penerima offline
- [ ] File/gambar di-upload ke S3 `chat/attachments/`
- [ ] `unread_count` di header conversation list selalu akurat
- [ ] Tidak bisa chat lintas sekolah (SchoolScope diterapkan)

## Tests to Write

```
tests/Feature/Chat/
  ConversationCreationTest.php
  SendMessageTest.php
  MarkAsReadTest.php
  UnreadCountTest.php
  CrossSchoolIsolationTest.php
  AttachmentUploadTest.php
```
