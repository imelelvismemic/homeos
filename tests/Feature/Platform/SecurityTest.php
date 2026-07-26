<?php

use App\Platform\Filament\Pages\Dashboard;
use App\Platform\Models\Household;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Route;

/**
 * Sigurnosni pregled Faze 9c, kao testovi a ne kao spisak namjera: granica koja
 * postoji samo u komentaru ruši se prvom izmjenom koja je ne primijeti.
 */
beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('app'));
});

it('rate limits every public route', function () {
    // Prijava/registracija/obnova lozinke imaju Filamentov rateLimit(5); ove tri
    // su naše javne rute i nisu imale nikakvo ograničenje do Faze 9c.
    $expected = [
        'household-invitation' => 'throttle:20,1',
        'locale' => 'throttle:30,1',
    ];

    foreach ($expected as $name => $middleware) {
        $route = Route::getRoutes()->getByName($name);

        expect($route)->not->toBeNull("Ruta ne postoji: {$name}");
        // `toContain` je varijadičan — poruka kao drugi argument bi bila DRUGA
        // očekivana vrijednost, pa se ovdje ne prosljeđuje.
        expect($route->gatherMiddleware())->toContain($middleware);
    }
});

it('deliberately leaves the health endpoint without a rate limiter', function () {
    // Rate limiter čuva brojače u cacheu. Na pokvarenom cacheu bi middleware pukao
    // prije kontrolera i endpoint bi vratio 500 — a njegov jedini posao u tom
    // trenutku je da prijavi `cache: false`. Zato je ovo izuzetak iz RULES.md §12,
    // i test ga drži izuzetkom NAMJERNO, da se ne "popravi" u tišini.
    $route = Route::getRoutes()->getByName('health');

    expect(collect($route->gatherMiddleware())->filter(fn ($m) => str_starts_with((string) $m, 'throttle')))
        ->toBeEmpty();
});

it('rate limits the endpoint that writes data from the quick-add modal', function () {
    $route = Route::getRoutes()->getByName('filament.app.quick-create');

    expect($route)->not->toBeNull();
    expect($route->gatherMiddleware())->toContain('throttle:60,1');
});

it('never lets a member reach another household through the search endpoint', function () {
    [$mine, $me] = makeHousehold();
    [$theirs] = makeHousehold();

    test()->actingAs($me->user);

    // `h` je parametar iz URL-a — bez provjere članstva bio bi to čitanje tuđih
    // podataka jednim ručno izmijenjenim linkom.
    test()->get(route('filament.app.search', ['h' => $theirs->getKey(), 'q' => 'test']))
        ->assertNotFound();

    test()->get(route('filament.app.search', ['h' => $mine->getKey(), 'q' => 'test']))
        ->assertOk();
});

it('never lets a member open another household panel page', function () {
    [, $me] = makeHousehold();
    [$theirs] = makeHousehold();

    test()->actingAs($me->user);

    expect($theirs)->toBeInstanceOf(Household::class);

    // Filament na tuđi tenant vraća 404, ne 403 — namjerno: 403 bi potvrdio da
    // domaćinstvo s tim ID-om postoji.
    test()->get(Dashboard::getUrl(tenant: $theirs))->assertNotFound();
});

it('requires the session to be signed in for panel pages', function () {
    [$household] = makeHousehold();

    test()->get(Dashboard::getUrl(tenant: $household))
        ->assertRedirect(route('filament.app.auth.login'));
});
