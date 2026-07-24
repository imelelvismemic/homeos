<?php

use App\Platform\Filament\Pages\Dashboard;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('app'));
    config()->set('homeos-apps', []); // 0 modula — DoD Faze 2
});

it('renders the Today dashboard with zero modules installed', function () {
    [$household, $owner] = makeHousehold();
    test()->actingAs($owner->user);
    Filament::setTenant($household);

    Livewire::test(Dashboard::class)
        ->assertOk()
        ->assertSee($owner->user->name)                 // pozdrav sadrži ime
        ->assertSee('Danas nema ništa hitno')           // prazan sažetak
        ->assertSee('Još nema instaliranih aplikacija'); // prazno stanje widgeta
});
