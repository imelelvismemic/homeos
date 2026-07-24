<?php

namespace App\Modules\Tasks\Listeners;

use App\Modules\Tasks\Events\TaskAssigned;
use App\Modules\Tasks\Notifications\TaskAssigned as TaskAssignedNotification;

/**
 * Auto-discoveran listener (CLAUDE.md §9): kad se zadatak dodijeli članu,
 * pošalje mu obavještenje (in-app uvijek, email po njegovoj preferenci).
 */
class NotifyAssignee
{
    public function handle(TaskAssigned $event): void
    {
        $assignee = $event->task->assignee;

        if ($assignee === null) {
            return;
        }

        $assignee->notify(new TaskAssignedNotification($event->task));
    }
}
