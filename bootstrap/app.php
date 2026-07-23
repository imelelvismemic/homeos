<?php

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
