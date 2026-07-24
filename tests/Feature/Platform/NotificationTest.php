<?php

use App\Platform\Events\Shared;
use App\Platform\Notifications\SharedWithYou;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\Fixtures\ShareableThing;

beforeEach(function () {
    createShareableThingsTable();
});

function thingSharedWith($member, $ownerUserId, $household): ShareableThing
{
    $thing = ShareableThing::create([
        'household_id' => $household->id,
        'created_by' => $ownerUserId,
        'title' => 'Zajednički dokument',
    ]);
    $thing->shareWith([$member]);

    return $thing;
}

it('emits the Shared event when an object is shared with members', function () {
    [$household, $owner, $members] = makeHousehold(extraMembers: 1);
    Event::fake([Shared::class]);

    thingSharedWith($members[0], $owner->user_id, $household);

    Event::assertDispatched(Shared::class);
});

it('sends the shared_with_you notification on both database and mail by default', function () {
    [$household, $owner, $members] = makeHousehold(extraMembers: 1);
    $recipient = $members[0];
    Notification::fake();

    thingSharedWith($recipient, $owner->user_id, $household);

    Notification::assertSentTo(
        $recipient,
        SharedWithYou::class,
        function ($notification, array $channels) {
            expect($channels)->toContain('database')->toContain('mail');

            return true;
        }
    );
});

it('omits email when the member disabled that category', function () {
    [$household, $owner, $members] = makeHousehold(extraMembers: 1);
    $recipient = $members[0];
    $recipient->notificationPreferences()->create([
        'category' => 'shared_with_you',
        'email_enabled' => false,
    ]);
    Notification::fake();

    thingSharedWith($recipient, $owner->user_id, $household);

    Notification::assertSentTo(
        $recipient,
        SharedWithYou::class,
        function ($notification, array $channels) {
            expect($channels)->toContain('database')->not->toContain('mail');

            return true;
        }
    );
});
