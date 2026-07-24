<?php

namespace App\Modules\Reminders\Listeners;

use App\Modules\Reminders\Events\ReminderFired;
use App\Modules\Reminders\Models\Reminder;
use App\Platform\Recurrence\RecurrenceService;

/**
 * Auto-discoveran listener: kad ponavljajući podsjetnik okine, kreira sljedeću
 * instancu s pomjerenim `due_date` (isti obrazac kao ponavljajući Task).
 */
class SpawnRecurringReminder
{
    public function __construct(private RecurrenceService $recurrence) {}

    public function handle(ReminderFired $event): void
    {
        $reminder = $event->reminder;

        if (! $reminder->isRecurring() || $reminder->due_date === null) {
            return;
        }

        $next = $this->recurrence->nextDueDate($reminder->recurrence_rule, $reminder->due_date);

        if ($next === null) {
            return;
        }

        Reminder::create([
            'household_id' => $reminder->household_id,
            'created_by' => $reminder->created_by,
            'title' => $reminder->title,
            'description' => $reminder->description,
            'due_date' => $next,
            'recurrence_rule' => $reminder->recurrence_rule,
            'remindable_type' => $reminder->remindable_type,
            'remindable_id' => $reminder->remindable_id,
        ]);
    }
}
