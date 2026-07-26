<?php

use App\Platform\Console\BackupCommand;
use App\Platform\Digest\DigestService;
use App\Platform\Enums\DigestFrequency;
use App\Platform\Scheduling\ModuleSchedule;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    // Platformske komande (homeos:backup) + komande modula (npr.
    // tasks:notify-due-soon) — svaki modul ih drži u svom Console/ folderu, pa
    // ih core pokupi bez hardkodovane liste.
    ->withCommands([
        __DIR__.'/../app/Platform/Console',
        ...(glob(__DIR__.'/../app/Modules/*/Console', GLOB_ONLYDIR) ?: []),
    ])
    // Event/Listener auto-discovery (CLAUDE.md §9): Laravel skenira ove foldere,
    // mapira listener po tipu u handle() i registruje ga — modul dodaje listener
    // u svoj Listeners/ folder i reaguje na tuđe evente bez diranja core-a.
    ->withEvents(discover: [
        __DIR__.'/../app/Platform/Listeners',
        ...(glob(__DIR__.'/../app/Modules/*/Listeners', GLOB_ONLYDIR) ?: []),
    ])
    // Centralni Scheduler (ROADMAP Faza 1.4): modul dodaje periodične zadatke
    // preko app/Modules/<Ime>/routes/schedule.php — bez diranja core-a.
    ->withSchedule(function (Schedule $schedule): void {
        ModuleSchedule::register($schedule, ModuleSchedule::moduleScheduleFiles());

        // Digest email (Faza 6) — platform-nivo (agregira sve module kroz
        // DigestSource registry), pa se planira ovdje, ne u modulu.
        $schedule->call(fn () => app(DigestService::class)->sendDue(DigestFrequency::Daily))
            ->dailyAt('07:00')
            ->name('digest-daily')
            ->withoutOverlapping();

        $schedule->call(fn () => app(DigestService::class)->sendDue(DigestFrequency::Weekly))
            ->weeklyOn(1, '07:30')
            ->name('digest-weekly')
            ->withoutOverlapping();

        // Dnevni backup baze i priloga (Faza 8) — u gluho doba, prije nego iko
        // počne raditi. Neuspjeh šalje email na adresu za tehnička upozorenja.
        $schedule->command(BackupCommand::class)
            ->dailyAt('03:15')
            ->name('homeos-backup')
            ->withoutOverlapping();
    })
    ->withMiddleware(function (Middleware $middleware): void {
        // Iza lanca Apache (SSL terminacija) → Nginx → PHP-FPM (CLAUDE.md §3a).
        // Bez ovoga Laravel vidi interni http saobraćaj i generiše http redirect
        // URL-ove iza https proxyja (redirect petlje / mixed content). App je
        // dostupan isključivo interno (Nginx na 127.0.0.1, app:9000 nije izložen),
        // pa je povjerenje svim proxyjima u ovoj topologiji sigurno. Apache vhost
        // mora slati "X-Forwarded-Proto: https" (vidi uputstvo za Virtualmin).
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
