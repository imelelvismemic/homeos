<?php

use App\Models\User;
use App\Platform\Filament\Tenancy\EditHouseholdProfile;
use App\Platform\Models\Household;
use Filament\Facades\Filament;
use Livewire\Features\SupportTesting\Testable;
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

/** Članovi domaćinstva žive na stranici postavki domaćinstva, ne kao zasebna stavka menija. */
function householdProfile(Household $household): Testable
{
    return Livewire::test(EditHouseholdProfile::class, ['tenant' => $household]);
}

it('lets the owner invite an existing registered user by email', function () {
    [$household, $owner] = makeHouseholdWithOwner();
    $invitee = User::factory()->create(['email' => 'invitee@example.com']);

    test()->actingAs($owner);
    Filament::setTenant($household);

    householdProfile($household)
        ->callTableAction('invite', data: [
            'email' => 'invitee@example.com',
            'role' => 'member',
        ])
        ->assertHasNoTableActionErrors();

    expect($household->members()->where('user_id', $invitee->id)->exists())->toBeTrue();
});

it('rejects inviting an email with no registered user', function () {
    [$household, $owner] = makeHouseholdWithOwner();

    test()->actingAs($owner);
    Filament::setTenant($household);

    householdProfile($household)
        ->callTableAction('invite', data: [
            'email' => 'nobody@example.com',
            'role' => 'member',
        ])
        ->assertHasTableActionErrors(['email']);
});

it('rejects inviting a user who is already a member', function () {
    [$household, $owner] = makeHouseholdWithOwner();

    test()->actingAs($owner);
    Filament::setTenant($household);

    householdProfile($household)
        ->callTableAction('invite', data: [
            'email' => $owner->email,
            'role' => 'member',
        ])
        ->assertHasTableActionErrors(['email']);
});

it('hides the invite action from a member who is not the household owner', function () {
    [$household, $owner] = makeHouseholdWithOwner();
    $member = User::factory()->create();
    $household->members()->create(['user_id' => $member->id, 'role' => 'member', 'joined_at' => now()]);

    test()->actingAs($owner);
    Filament::setTenant($household);
    householdProfile($household)->assertTableActionVisible('invite');

    test()->actingAs($member);
    householdProfile($household)->assertTableActionHidden('invite');
});

it('shows every member of the household to a plain member too', function () {
    [$household, $owner] = makeHouseholdWithOwner();
    $member = User::factory()->create(['name' => 'Drugi član']);
    $household->members()->create(['user_id' => $member->id, 'role' => 'member', 'joined_at' => now()]);

    test()->actingAs($member);
    Filament::setTenant($household);

    householdProfile($household)
        ->assertOk()
        ->assertCanSeeTableRecords($household->members()->get())
        ->assertSee($owner->name);
});

it('does not let a member of another household see this household members', function () {
    [$household] = makeHouseholdWithOwner();
    [$otherHousehold, $otherOwner] = makeHouseholdWithOwner();

    expect($household->users()->whereKey($otherOwner->id)->exists())->toBeFalse();
    expect($otherOwner->can('view', $household))->toBeFalse();
    expect(EditHouseholdProfile::canView($household))->toBeFalse();
});
