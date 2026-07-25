<?php

namespace App\Modules\Reminders\Console;

use App\Modules\Reminders\Models\Reminder;
use App\Modules\Reminders\Services\ReminderFirer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Okida podsjetnike kojima je došao `due_date`. Pokreće ga centralni scheduler
 * svake minute; samo okidanje je u ReminderFirer servisu (isti put kao ručno
 * okidanje iz UI-a).
 *
 * Greška na jednom podsjetniku (npr. pad slanja emaila) se loguje i ne prekida
 * obradu ostalih — `completed_at` je već upisan, pa se isti podsjetnik neće
 * ponovo okinuti (i ponovo obavijestiti) u sljedećoj minuti.
 */
class FireDueRemindersCommand extends Command
{
    protected $signature = 'reminders:fire';

    protected $description = 'Okini podsjetnike kojima je došlo vrijeme';

    public function handle(ReminderFirer $firer): int
    {
        $due = Reminder::query()
            ->whereNull('completed_at')
            ->whereNotNull('due_date')
            ->where('due_date', '<=', now())
            ->get();

        $fired = 0;

        foreach ($due as $reminder) {
            try {
                $fired += $firer->fire($reminder) ? 1 : 0;
            } catch (Throwable $e) {
                Log::error('Okidanje podsjetnika nije uspjelo', [
                    'reminder_id' => $reminder->getKey(),
                    'exception' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Okinuto podsjetnika: {$fired}");

        return self::SUCCESS;
    }
}
