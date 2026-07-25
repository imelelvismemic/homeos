<?php

namespace App\Modules\Reminders\Services;

use App\Modules\Reminders\Events\ReminderFired;
use App\Modules\Reminders\Models\Reminder;

/**
 * Jedino mjesto na kojem se podsjetnik "okida" (CLAUDE.md §15 — logika u servisu,
 * ne u komandi/Resource-u). Koriste ga i scheduler (FireDueRemindersCommand) i
 * ručna akcija u UI-u, pa je ponašanje isto bez obzira odakle dolazi:
 *
 *  1. `completed_at` se upisuje PRIJE eventa — ako slanje obavještenja padne
 *     (npr. mail provider nedostupan), scheduler NE ponavlja isti podsjetnik
 *     svake minute. Ovo je bio uzrok dupliranih obavještenja u sanducetu.
 *  2. Tek onda ide ReminderFired → obavještenje članu + sljedeća instanca
 *     ponavljajućeg podsjetnika.
 *
 * Već okinut podsjetnik se ignoriše (idempotentno).
 */
class ReminderFirer
{
    public function fire(Reminder $reminder): bool
    {
        if ($reminder->completed_at !== null) {
            return false;
        }

        $reminder->update(['completed_at' => now()]);

        ReminderFired::dispatch($reminder);

        return true;
    }
}
