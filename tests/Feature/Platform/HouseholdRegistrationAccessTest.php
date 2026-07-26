<?php

use App\Models\User;
use App\Platform\Filament\Pages\RegisterHousehold;
use Filament\Facades\Filament;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('app'));
});

it('opens the create-household form for a user who has no household', function () {
    $user = User::factory()->create();

    test()->actingAs($user);

    expect(RegisterHousehold::canView())->toBeTrue();

    // Regresija: korisnički meni se renderuje i na ovoj stranici, a stavka
    // "Moj profil" gradi URL s {tenant} parametrom — bez guarda je stranica
    // vraćala 500 (prijavljeno s produkcije).
    test()->get(filament()->getTenantRegistrationUrl())->assertOk();
});

it('sends a user with no household to the create-household form after login', function () {
    $user = User::factory()->create();

    test()->actingAs($user)
        ->get('/')
        ->assertRedirect(filament()->getTenantRegistrationUrl());
});

it('hides the create-household form from a user who already has a household', function () {
    [$household, $owner] = makeHousehold();

    test()->actingAs($owner->user);

    // Nema više "novo domaćinstvo" u padajućem meniju (Filament ga krije po
    // canView), niti se do forme može direktnim URL-om.
    expect(RegisterHousehold::canView())->toBeFalse();

    test()->get(filament()->getTenantRegistrationUrl())->assertNotFound();
});

it('hides the create-household form from an invited member who owns nothing', function () {
    [$household] = makeHousehold();
    $member = User::factory()->create();
    $household->members()->create(['user_id' => $member->id, 'role' => 'member', 'joined_at' => now()]);

    test()->actingAs($member);

    // Pozvani član nema svoje domaćinstvo, ali ima gdje raditi — ne tjeramo ga
    // da kreira vlastito.
    expect($member->ownedHouseholds()->doesntExist())->toBeTrue();
    expect(RegisterHousehold::canView())->toBeFalse();
});
