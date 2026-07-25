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

        // Ako je podsjetnik dugo stajao neokinut (ili je okinut ručno prije roka),
        // sljedeći termin može ispasti u prošlosti — tada bi scheduler odmah okinuo
        // i novu instancu, pa opet, i tako u nizu obavještenja. Preskoči na prvi
        // termin u budućnosti (granica čuva od beskonačne petlje kod loše rule).
        for ($guard = 0; $guard < 1000 && $next->isPast(); $guard++) {
            $following = $this->recurrence->nextDueDate($reminder->recurrence_rule, $next);

            if ($following === null || $following->lessThanOrEqualTo($next)) {
                break;
            }

            $next = $following;
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
