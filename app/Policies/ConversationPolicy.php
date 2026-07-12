<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;

class ConversationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('conversation.view_any');
    }

    public function view(User $user, Conversation $conversation): bool
    {
        return $user->can('conversation.view');
    }

    public function create(User $user): bool
    {
        return $user->can('conversation.create');
    }

    public function update(User $user, Conversation $conversation): bool
    {
        return $user->can('conversation.update');
    }

    public function delete(User $user, Conversation $conversation): bool
    {
        return $user->can('conversation.delete');
    }
}
