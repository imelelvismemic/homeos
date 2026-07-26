<?php

use App\Platform\Filament\Pages\Dashboard;
use Filament\Facades\Filament;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('app'));
    config()->set('homeos-apps', []); // 0 modula — DoD Faze 2
});

it('greets by local time of day, not by UTC (PRAVILA.md §7)', function () {
    [$household, $owner] = makeHousehold();
    test()->actingAs($owner->user);
    Filament::setTenant($household);

    // Jutro u lokalnoj zoni; kad je aplikacija bila zaključana na UTC, ovo je
    // ispadalo "Dobro veče" jer je 06:55 lokalno bilo 04:55 UTC.
    foreach ([['06:55', 'Dobro jutro'], ['13:00', 'Dobar dan'], ['21:30', 'Dobro veče'], ['02:15', 'Dobro veče']] as [$time, $expected]) {
        Carbon::setTestNow(Carbon::today()->setTimeFromTimeString($time));

        expect(app(Dashboard::class)->heroGreeting())->toStartWith($expected);
    }

    Carbon::setTestNow();
});

it('renders the Today dashboard with zero modules installed', function () {
    [$household, $owner] = makeHousehold();
    test()->actingAs($owner->user);
    Filament::setTenant($household);

    Livewire::test(Dashboard::class)
        ->assertOk()
        ->assertSee($owner->user->name)                 // pozdrav sadrži ime
        ->assertSee('Danas nema ništa hitno')           // prazan sažetak
        ->assertSee('Nema uključenih aplikacija'); // prazno stanje widgeta
});

it('separates "no apps enabled" from "apps on, nothing to show today"', function () {
    config()->set('homeos-apps', require base_path('config/homeos-apps.php'));

    [$household, $owner] = makeHousehold();
    test()->actingAs($owner->user);
    Filament::setTenant($household);

    // Moduli su uključeni, ali domaćinstvo još nema nijedan podatak — poruka
    // ne smije tvrditi da aplikacije nisu instalirane.
    Livewire::test(Dashboard::class)
        ->assertOk()
        ->assertSee('Danas nema šta prikazati')
        ->assertDontSee('Nema uključenih aplikacija');
});
