<?php

use App\Models\User;
use App\Platform\Filament\Pages\RegisterHousehold;
use App\Platform\Models\Household;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('app'));
});

it('lets a logged in user create a household and become its owner', function () {
    $user = User::factory()->create();
    test()->actingAs($user);

    Livewire::test(RegisterHousehold::class)
        ->set('data.name', 'Memić domaćinstvo')
        ->call('register')
        ->assertHasNoFormErrors();

    $household = Household::where('name', 'Memić domaćinstvo')->first();

    expect($household)->not->toBeNull();
    expect($household->owner_id)->toBe($user->id);

    $membership = $household->members()->where('user_id', $user->id)->first();
    expect($membership)->not->toBeNull();
    expect($membership->role)->toBe('owner');

    expect($user->refresh()->current_household_id)->toBe($household->id);
});
