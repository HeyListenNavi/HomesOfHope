<?php

namespace App\Policies;

use App\Models\Message;
use App\Models\User;

class MessagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('message.view_any');
    }

    public function view(User $user, Message $message): bool
    {
        return $user->can('message.view');
    }

    public function create(User $user): bool
    {
        return $user->can('message.create');
    }

    public function update(User $user, Message $message): bool
    {
        return $user->can('message.update');
    }

    public function delete(User $user, Message $message): bool
    {
        return $user->can('message.delete');
    }
}
