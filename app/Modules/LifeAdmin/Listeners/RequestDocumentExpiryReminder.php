<?php

namespace App\Modules\LifeAdmin\Listeners;

use App\Modules\LifeAdmin\Events\DocumentCreated;
use App\Platform\Events\ReminderRequested;

/**
 * DoD Faze 5b: kad se kreira dokument s datumom isteka, Life admin TRAŽI
 * podsjetnik preko platform eventa (X dana prije isteka). Reminders ga kreira,
 * scheduler okine → reminder_fired email. Ništa van modula Life admin — nema
 * importa Reminders (isti mehanizam kao računi u Finansijama).
 */
class RequestDocumentExpiryReminder
{
    public function handle(DocumentCreated $event): void
    {
        $document = $event->document;

        $reminderDate = $document->reminderDate();

        if ($reminderDate === null) {
            return;
        }

        ReminderRequested::dispatch(
            $document,
            $reminderDate,
            __('lifeadmin.reminder.document_expiring', ['title' => $document->title]),
        );
    }
}
