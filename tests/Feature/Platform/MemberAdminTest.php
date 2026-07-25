<?php

use App\Platform\Models\Household;
use App\Platform\Models\HouseholdMember;
use App\Platform\Services\HouseholdMemberService;

it('changes a member role', function () {
    [$household, $owner, $members] = makeHousehold(extraMembers: 1);
    $member = $members[0];

    app(HouseholdMemberService::class)->changeRole($member, 'owner');

    expect($member->fresh()->role)->toBe('owner');
});

it('removes a member', function () {
    [$household, $owner, $members] = makeHousehold(extraMembers: 1);
    $member = $members[0];

    app(HouseholdMemberService::class)->remove($member);

    expect(HouseholdMember::find($member->id))->toBeNull();
});

it('refuses to demote or remove the last owner', function () {
    [$household, $owner] = makeHousehold();
    $service = app(HouseholdMemberService::class);

    expect(fn () => $service->changeRole($owner, 'member'))
        ->toThrow(RuntimeException::class);
    expect(fn () => $service->remove($owner))
        ->toThrow(RuntimeException::class);

    expect($owner->fresh()->role)->toBe('owner');
    expect(HouseholdMember::find($owner->id))->not->toBeNull();
});

it('transfers ownership and updates the household owner', function () {
    [$household, $owner, $members] = makeHousehold(extraMembers: 1);
    $member = $members[0];

    app(HouseholdMemberService::class)->transferOwnership($member, $owner);

    expect($member->fresh()->role)->toBe('owner');
    expect($owner->fresh()->role)->toBe('member');
    expect(Household::find($household->id)->owner_id)->toBe($member->user_id);
});
