<?php

use App\Models\Communication\Conversation;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Mobile chat: client subscribes to `private-conversation.{id}`.
// Only participants of the same school may listen.
Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    $conv = Conversation::withoutGlobalScopes()->find($conversationId);
    if (! $conv || (int) $conv->school_id !== (int) $user->school_id) {
        return false;
    }
    return (int) $conv->user_one === (int) $user->id || (int) $conv->user_two === (int) $user->id;
});
