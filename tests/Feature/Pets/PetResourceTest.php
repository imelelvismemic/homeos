<?php

use App\Modules\Pets\Enums\PetSpecies;
use App\Modules\Pets\Filament\Resources\PetResource\Pages\CreatePet;
use App\Modules\Pets\Filament\Resources\PetResource\Pages\ListPets;
use App\Modules\Pets\Models\Pet;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('app'));
});

it('creates a pet through the resource, stamping household and creator', function () {
    [$household, $owner] = makeHousehold();
    test()->actingAs($owner->user);
    Filament::setTenant($household);

    Livewire::test(CreatePet::class)
        ->fillForm([
            'name' => 'Luna',
            'species' => PetSpecies::Dog->value,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $pet = Pet::firstWhere('name', 'Luna');

    expect($pet)->not->toBeNull();
    expect($pet->household_id)->toBe($household->id);
    expect($pet->created_by)->toBe($owner->user_id);
});

it('never shows a pet to a member of another household', function () {
    [$household, $owner] = makeHousehold();
    Pet::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'name' => 'Luna',
        'species' => PetSpecies::Dog,
    ]);

    [$otherHousehold, $otherOwner] = makeHousehold();
    test()->actingAs($otherOwner->user);
    Filament::setTenant($otherHousehold);

    Livewire::test(ListPets::class)->assertCanNotSeeTableRecords(Pet::all());
});
