<?php

use App\Platform\Filament\Pages\Dashboard;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('app'));
    config()->set('homeos-apps', []);
});

it('translates the confirmation-mismatch validation message to Bosnian', function () {
    // Bez lang/bs/validation.php ovo bi bilo na engleskom (miješani tekst na
    // registraciji/obnovi šifre).
    $message = trans('validation.same', ['attribute' => 'šifra', 'other' => 'potvrda šifre']);

    expect($message)->toContain('podudarati');
    expect($message)->not->toContain('match');
});

it('greets by time of day, treating after-midnight hours as evening', function () {
    [$household, $owner] = makeHousehold();
    test()->actingAs($owner->user);
    Filament::setTenant($household);

    $cases = [
        3 => 'Dobro veče',   // noć nije jutro
        8 => 'Dobro jutro',
        14 => 'Dobar dan',
        21 => 'Dobro veče',
    ];

    foreach ($cases as $hour => $expected) {
        test()->travelTo(now()->startOfDay()->addHours($hour));

        Livewire::test(Dashboard::class)->assertSee($expected);

        test()->travelBack();
    }
});
