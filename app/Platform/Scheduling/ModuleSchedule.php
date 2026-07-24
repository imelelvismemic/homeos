<?php

namespace App\Platform\Scheduling;

use Illuminate\Console\Scheduling\Schedule;

/**
 * Centralni scheduler mehanizam (ROADMAP Faza 1.4, CLAUDE.md §14): modul
 * registruje svoje periodične zadatke tako što doda fajl
 * `app/Modules/<Ime>/routes/schedule.php` koji VRAĆA closure:
 *
 *     <?php
 *     use Illuminate\Console\Scheduling\Schedule;
 *     return function (Schedule $schedule): void {
 *         $schedule->command('bills:check-due')->dailyAt('08:00');
 *     };
 *
 * Nema izmjene core-a — samo se doda fajl. Scheduler kontejner (schedule:work)
 * ga pokupi. bootstrap/app.php poziva register() sa moduleScheduleFiles().
 */
class ModuleSchedule
{
    /**
     * @param  array<int, string>  $files
     */
    public static function register(Schedule $schedule, array $files): void
    {
        foreach ($files as $file) {
            if (! is_file($file)) {
                continue;
            }

            $callback = require $file;

            if (is_callable($callback)) {
                $callback($schedule);
            }
        }
    }

    /**
     * @return array<int, string>
     */
    public static function moduleScheduleFiles(): array
    {
        return glob(app_path('Modules/*/routes/schedule.php')) ?: [];
    }
}
