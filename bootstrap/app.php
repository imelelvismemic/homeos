<?php

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
    // Artisan komande modula (npr. tasks:notify-due-soon) — svaki modul ih drži
    // u svom Console/ folderu; core ih pokupi bez hardkodovane liste.
    ->withCommands(glob(__DIR__.'/../app/Modules/*/Console', GLOB_ONLYDIR) ?: [])
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
