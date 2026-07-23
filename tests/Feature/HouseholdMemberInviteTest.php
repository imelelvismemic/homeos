<?php

use App\Models\User;
use App\Platform\Filament\Resources\HouseholdMemberResource\Pages\ListHouseholdMembers;
use App\Platform\Models\Household;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('app'));
});

function makeHouseholdWithOwner(): array
{
    $owner = User::factory()->create();
    $household = Household::create(['name' => 'Test domaćinstvo', 'owner_id' => $owner->id]);
    $household->members()->create(['user_id' => $owner->id, 'role' => 'owner', 'joined_at' => now()]);
    $owner->update(['current_household_id' => $household->id]);

    return [$household, $owner];
}

it('lets the owner invite an existing registered user by email', function () {
    [$household, $owner] = makeHouseholdWithOwner();
    $invitee = User::factory()->create(['email' => 'invitee@example.com']);

    test()->actingAs($owner);
    Filament::setTenant($household);

    Livewire::test(ListHouseholdMembers::class)
        ->callAction('invite', data: [
            'email' => 'invitee@example.com',
            'role' => 'member',
        ])
        ->assertHasNoActionErrors();

    expect($household->members()->where('user_id', $invitee->id)->exists())->toBeTrue();
});

it('rejects inviting an email with no registered user', function () {
    [$household, $owner] = makeHouseholdWithOwner();

    test()->actingAs($owner);
    Filament::setTenant($household);

    Livewire::test(ListHouseholdMembers::class)
        ->callAction('invite', data: [
            'email' => 'nobody@example.com',
            'role' => 'member',
        ])
        ->assertHasActionErrors(['email']);
});

it('rejects inviting a user who is already a member', function () {
    [$household, $owner] = makeHouseholdWithOwner();

    test()->actingAs($owner);
    Filament::setTenant($household);

    Livewire::test(ListHouseholdMembers::class)
        ->callAction('invite', data: [
            'email' => $owner->email,
            'role' => 'member',
        ])
        ->assertHasActionErrors(['email']);
});

it('does not let a member of another household see this household members', function () {
    [$household] = makeHouseholdWithOwner();
    [$otherHousehold, $otherOwner] = makeHouseholdWithOwner();

    expect($household->users()->whereKey($otherOwner->id)->exists())->toBeFalse();
    expect($otherOwner->can('view', $household))->toBeFalse();
});
