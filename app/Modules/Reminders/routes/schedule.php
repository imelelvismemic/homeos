<?php

use App\Modules\Reminders\Console\FireDueRemindersCommand;
use Illuminate\Console\Scheduling\Schedule;

/**
 * Periodični zadaci modula Podsjetnici (CLAUDE.md §14). Podsjetnici su
 * vremenski osjetljivi, pa se provjera radi svake minute.
 */
return function (Schedule $schedule): void {
    $schedule->command(FireDueRemindersCommand::class)->everyMinute();
};
