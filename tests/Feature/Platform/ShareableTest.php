<?php

use App\Platform\Enums\Visibility;
use Tests\Fixtures\ShareableThing;

beforeEach(function () {
    createShareableThingsTable();
});

function makeThing($household, $ownerUserId): ShareableThing
{
    return ShareableThing::create([
        'household_id' => $household->id,
        'created_by' => $ownerUserId,
        'title' => 'Nešto',
    ]);
}

it('creates a household-visible share by default', function () {
    [$household, $owner, $members] = makeHousehold(extraMembers: 1);
    $other = $members[0];

    $thing = makeThing($household, $owner->user_id);

    expect($thing->share)->not->toBeNull();
    expect($thing->share->visibility)->toBe(Visibility::Household);
    // Oba člana domaćinstva vide household-vidljiv objekat.
    expect($thing->isVisibleTo($owner->user))->toBeTrue();
    expect($thing->isVisibleTo($other->user))->toBeTrue();
});

it('makes an object private to its owner only', function () {
    [$household, $owner, $members] = makeHousehold(extraMembers: 1);
    $other = $members[0];

    $thing = makeThing($household, $owner->user_id);
    $thing->makePrivate();

    expect($thing->share->visibility)->toBe(Visibility::Private);
    expect($thing->isVisibleTo($owner->user))->toBeTrue();
    expect($thing->isVisibleTo($other->user))->toBeFalse();
});

it('shares with specific members only', function () {
    [$household, $owner, $members] = makeHousehold(extraMembers: 2);
    [$invited, $notInvited] = $members;

    $thing = makeThing($household, $owner->user_id);
    $thing->shareWith([$invited]);

    expect($thing->share->visibility)->toBe(Visibility::Specific);
    expect($thing->isVisibleTo($owner->user))->toBeTrue();      // owner uvijek
    expect($thing->isVisibleTo($invited->user))->toBeTrue();    // pozvani
    expect($thing->isVisibleTo($notInvited->user))->toBeFalse(); // ostali ne
});

it('never leaks a household object to a member of another household', function () {
    [$household, $owner] = makeHousehold();
    [$otherHousehold, $stranger] = makeHousehold();

    $thing = makeThing($household, $owner->user_id); // default household-visible

    expect($thing->isVisibleTo($stranger->user))->toBeFalse();
});

it('scopes queries to what is visible within the household', function () {
    [$household, $owner, $members] = makeHousehold(extraMembers: 1);
    $other = $members[0];

    $shared = makeThing($household, $owner->user_id);        // household-visible
    $ownerPrivate = makeThing($household, $owner->user_id);
    $ownerPrivate->makePrivate();

    // Drugi član vidi dijeljeno, ali ne i vlasnikovo privatno.
    $visibleToOther = ShareableThing::visibleTo($other->user)->pluck('id');
    expect($visibleToOther)->toContain($shared->id);
    expect($visibleToOther)->not->toContain($ownerPrivate->id);

    // Vlasnik vidi oba svoja.
    expect(ShareableThing::visibleTo($owner->user)->pluck('id'))
        ->toContain($shared->id)
        ->toContain($ownerPrivate->id);
});
