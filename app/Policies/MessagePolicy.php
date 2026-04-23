<?php

namespace App\Policies;

use App\Models\Message;
use App\Models\User;
use App\Models\DirectConversation;

class MessagePolicy
{
    public function view(User $user, Message $message): bool
    {
        if ($message->chat_room_id) {
            return $message->chatRoom()->whereHas('members', fn ($q) => $q->where('users.id', $user->id))->exists();
        }

        if ($message->direct_conversation_id) {
            return $message->directConversation()->where(fn ($q) => $q
                ->where('user_one_id', $user->id)
                ->orWhere('user_two_id', $user->id)
            )->exists();
        }

        return false;
    }

    public function update(User $user, Message $message): bool
    {
        return $message->sender_id === $user->id;
    }

    public function delete(User $user, Message $message): bool
    {
        return $message->sender_id === $user->id;
    }

    public function sendDirect(User $user, DirectConversation $conversation): bool
    {
        return $conversation->hasParticipant($user);
    }
}
