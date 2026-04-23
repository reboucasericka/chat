<?php

namespace App\Policies;

use App\Models\DirectConversation;
use App\Models\User;

class DirectConversationPolicy
{
    public function view(User $user, DirectConversation $directConversation): bool
    {
        return $directConversation->hasParticipant($user);
    }

    public function sendMessage(User $user, DirectConversation $directConversation): bool
    {
        return $directConversation->hasParticipant($user);
    }

    public function delete(User $user, DirectConversation $directConversation): bool
    {
        return $directConversation->hasParticipant($user);
    }
}
