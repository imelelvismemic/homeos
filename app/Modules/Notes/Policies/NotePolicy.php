<?php

namespace App\Modules\Notes\Policies;

use App\Models\User;
use App\Modules\Notes\Models\Note;

/**
 * Autorizacija kroz Shareable mehanizam (CLAUDE.md §11) — vidi TaskPolicy.
 */
class NotePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Note $note): bool
    {
        return $note->isVisibleTo($user);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Note $note): bool
    {
        return $note->isVisibleTo($user);
    }

    public function delete(User $user, Note $note): bool
    {
        return $note->isVisibleTo($user);
    }
}
