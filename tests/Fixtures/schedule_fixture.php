<?php

use Illuminate\Console\Scheduling\Schedule;

// Simulira app/Modules/<Ime>/routes/schedule.php — vraća closure koji registruje
// periodični zadatak. Koristi se u SchedulerTest.
return function (Schedule $schedule): void {
    $schedule->command('inspire')->daily();
};
