<?php

use App\Models\User;
use App\Platform\Filament\Tenancy\EditHouseholdProfile;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('app'));
});

it('lets the owner rename the household', function () {
    [$household, $owner] = makeHousehold();
    test()->actingAs($owner->user);
    Filament::setTenant($household);

    Livewire::test(EditHouseholdProfile::class, ['tenant' => $household])
        ->fillForm(['name' => 'Memić domaćinstvo'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($household->fresh()->name)->toBe('Memić domaćinstvo');
});

it('does not let a plain member rename the household', function () {
    [$household] = makeHousehold();
    $member = User::factory()->create();
    $household->members()->create(['user_id' => $member->id, 'role' => 'member', 'joined_at' => now()]);

    test()->actingAs($member);
    Filament::setTenant($household);

    expect(EditHouseholdProfile::canView($household))->toBeFalse();
});
