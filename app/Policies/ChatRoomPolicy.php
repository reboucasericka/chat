<?php

namespace App\Policies;

use App\Models\ChatRoom;
use App\Models\User;

class ChatRoomPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->id > 0;
    }

    public function view(User $user, ChatRoom $chatRoom): bool
    {
        return $user->id > 0;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function manageMembers(User $user, ChatRoom $chatRoom): bool
    {
        return $user->isAdmin();
    }

    public function sendMessage(User $user, ChatRoom $chatRoom): bool
    {
        return $chatRoom->members()->where('users.id', $user->id)->exists();
    }

    public function delete(User $user, ChatRoom $chatRoom): bool
    {
        return $user->isAdmin();
    }
}
