<?php

use App\Modules\Reminders\Events\ReminderCreated;
use App\Modules\Reminders\Models\Reminder;
use App\Modules\Reminders\Notifications\ReminderDue;
use App\Platform\Enums\Visibility;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;

it('creates a reminder with a household-visible share', function () {
    [$household, $owner] = makeHousehold();

    $reminder = Reminder::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'title' => 'Uzeti lijek',
        'due_date' => now()->addHours(2),
    ]);

    expect($reminder->exists)->toBeTrue();
    expect($reminder->share->visibility)->toBe(Visibility::Household);
});

it('dispatches ReminderCreated on creation', function () {
    [$household, $owner] = makeHousehold();
    Event::fake([ReminderCreated::class]);

    $reminder = Reminder::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'title' => 'Zvati doktora',
        'due_date' => now()->addDay(),
    ]);

    Event::assertDispatched(ReminderCreated::class, fn ($e) => $e->reminder->is($reminder));
});

it('fires due reminders via scheduler: notifies creator and marks completed', function () {
    [$household, $owner] = makeHousehold();
    Notification::fake();

    $due = Reminder::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'title' => 'Prošlo vrijeme',
        'due_date' => now()->subMinute(),
    ]);

    $future = Reminder::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'title' => 'Budući',
        'due_date' => now()->addDay(),
    ]);

    test()->artisan('reminders:fire')->assertSuccessful();

    expect($due->fresh()->completed_at)->not->toBeNull();
    expect($future->fresh()->completed_at)->toBeNull();

    Notification::assertSentTo($owner, ReminderDue::class, fn (ReminderDue $n) => $n->reminder->is($due));
});

it('notifies the assigned member instead of the creator when set', function () {
    [$household, $owner, $members] = makeHousehold(extraMembers: 1);
    $assignee = $members[0];
    Notification::fake();

    Reminder::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'assigned_to' => $assignee->id,
        'title' => 'Za drugog člana',
        'due_date' => now()->subMinute(),
    ]);

    test()->artisan('reminders:fire')->assertSuccessful();

    Notification::assertSentTo($assignee, ReminderDue::class);
    Notification::assertNotSentTo($owner, ReminderDue::class);
});

it('spawns the next instance when a recurring reminder fires', function () {
    [$household, $owner] = makeHousehold();
    Notification::fake();

    $due = Reminder::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'title' => 'Zalij biljke',
        'due_date' => now()->subMinute(),
        'recurrence_rule' => 'FREQ=DAILY',
    ]);

    test()->artisan('reminders:fire')->assertSuccessful();

    $next = Reminder::query()->whereKeyNot($due->id)->latest('id')->first();

    expect($next)->not->toBeNull();
    expect($next->completed_at)->toBeNull();
    expect($next->due_date->toDateString())->toBe($due->due_date->copy()->addDay()->toDateString());
    expect($due->fresh()->completed_at)->not->toBeNull();
});
