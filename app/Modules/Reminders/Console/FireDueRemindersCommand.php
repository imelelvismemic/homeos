<?php

namespace App\Modules\Reminders\Console;

use App\Modules\Reminders\Events\ReminderFired;
use App\Modules\Reminders\Models\Reminder;
use Illuminate\Console\Command;

/**
 * Okida podsjetnike kojima je došao `due_date`. Pokreće ga centralni scheduler
 * svake minute. Za svaki: ReminderFired event (→ notifikacija + spawn sljedeće
 * ponavljajuće instance), pa se označi kao okinut (completed_at).
 */
class FireDueRemindersCommand extends Command
{
    protected $signature = 'reminders:fire';

    protected $description = 'Okini podsjetnike kojima je došlo vrijeme';

    public function handle(): int
    {
        $due = Reminder::query()
            ->whereNull('completed_at')
            ->whereNotNull('due_date')
            ->where('due_date', '<=', now())
            ->get();

        foreach ($due as $reminder) {
            // Event prvi (spawn sljedeće instance čita due_date), pa označi okinutim.
            ReminderFired::dispatch($reminder);
            $reminder->update(['completed_at' => now()]);
        }

        $this->info("Okinuto podsjetnika: {$due->count()}");

        return self::SUCCESS;
    }
}
