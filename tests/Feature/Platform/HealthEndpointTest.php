<?php

use Illuminate\Support\Facades\DB;

it('reports healthy with the app version', function () {
    test()->getJson('/zdravlje')
        ->assertOk()
        ->assertJson([
            'status' => 'ok',
            'version' => config('homeos.version'),
            'checks' => ['database' => true, 'cache' => true, 'storage' => true],
        ]);
});

it('is reachable without logging in (deploy and uptime monitor use it)', function () {
    test()->get('/zdravlje')->assertOk();
});

it('answers 503 when a dependency is down, not a cheerful 200', function () {
    // Deploy i monitor moraju razlikovati "stranica se renderuje" od "sistem radi".
    DB::shouldReceive('connection')->andThrow(new RuntimeException('baza nedostupna'));

    test()->getJson('/zdravlje')
        ->assertStatus(503)
        ->assertJson(['status' => 'degraded', 'checks' => ['database' => false]]);
});
