<?php

namespace App\Modules\Reminders\Policies;

use App\Models\User;
use App\Modules\Reminders\Models\Reminder;

/**
 * Autorizacija kroz Shareable mehanizam (CLAUDE.md §11) — vidi TaskPolicy.
 */
class ReminderPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Reminder $reminder): bool
    {
        return $reminder->isVisibleTo($user);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Reminder $reminder): bool
    {
        return $reminder->isVisibleTo($user);
    }

    public function delete(User $user, Reminder $reminder): bool
    {
        return $reminder->isVisibleTo($user);
    }
}
