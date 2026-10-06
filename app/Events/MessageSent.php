<?php

namespace App\Events;

use App\Models\Communication\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Message $message) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('conversation.'.$this->message->conversation_id);
    }

    public function broadcastAs(): string
    {
        return 'message.new';
    }

    public function broadcastWith(): array
    {
        $m = $this->message->loadMissing('sender');
        return [
            'id' => $m->id,
            'conversation_id' => $m->conversation_id,
            'sender_id' => $m->sender_id,
            'sender' => $m->sender ? ['id' => $m->sender->id, 'name' => $m->sender->name] : null,
            'body' => $m->body,
            'file' => $m->file,
            'created_at' => $m->created_at?->toIso8601String(),
        ];
    }
}
