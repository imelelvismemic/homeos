<?php

use App\Platform\Scheduling\ModuleSchedule;
use Illuminate\Console\Scheduling\Schedule;

it('lets a module register a scheduled task without touching core', function () {
    $schedule = app(Schedule::class);
    $before = count($schedule->events());

    // Isto što bootstrap/app.php radi za app/Modules/<Ime>/routes/schedule.php.
    ModuleSchedule::register($schedule, [__DIR__.'/../../Fixtures/schedule_fixture.php']);

    expect(count($schedule->events()))->toBeGreaterThan($before);
});
