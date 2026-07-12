<?php

namespace App\Policies;

use App\Models\Question;
use App\Models\User;

class QuestionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('question.view_any');
    }

    public function view(User $user, Question $question): bool
    {
        return $user->can('question.view');
    }

    public function create(User $user): bool
    {
        return $user->can('question.create');
    }

    public function update(User $user, Question $question): bool
    {
        return $user->can('question.update');
    }

    public function delete(User $user, Question $question): bool
    {
        return $user->can('question.delete');
    }
}
