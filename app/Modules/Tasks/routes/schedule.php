<?php

use App\Modules\Tasks\Console\NotifyDueSoonCommand;
use Illuminate\Console\Scheduling\Schedule;

/**
 * Periodični zadaci modula Zadaci (CLAUDE.md §14, centralni scheduler).
 * Jednom dnevno u 08:00 provjeri zadatke koji ističu u narednih 24h i
 * obavijesti odgovorne osobe.
 */
return function (Schedule $schedule): void {
    $schedule->command(NotifyDueSoonCommand::class)->dailyAt('08:00');
};
