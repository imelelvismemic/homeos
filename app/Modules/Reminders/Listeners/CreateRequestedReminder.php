<?php

namespace App\Modules\Reminders\Listeners;

use App\Modules\Reminders\Models\Reminder;
use App\Platform\Events\ReminderRequested;
use App\Platform\Sharing\VisibilityMirror;

/**
 * Auto-discoveran listener (CLAUDE.md §9): kreira podsjetnik vezan za entitet
 * koji je neki drugi modul "prijavio" preko ReminderRequested eventa. Reminders
 * ne importuje taj modul — radi generički s Model instancom iz eventa.
 */
class CreateRequestedReminder
{
    public function handle(ReminderRequested $event): void
    {
        $remindable = $event->remindable;

        $reminder = Reminder::create([
            'household_id' => $remindable->household_id,
            'created_by' => auth()->id() ?? $remindable->created_by,
            'assigned_to' => $event->assignedTo,
            'title' => $event->title,
            'description' => $event->description,
            'due_date' => $event->dueDate,
            'remindable_type' => $remindable->getMorphClass(),
            'remindable_id' => $remindable->getKey(),
        ]);

        // Podsjetnik nasljeđuje vidljivost izvora (privatan račun → privatan
        // podsjetnik) — inače bi naslov (naziv izvora) procurio domaćinstvu.
        VisibilityMirror::mirror($remindable, $reminder);
    }
}
