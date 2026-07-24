<?php

use App\Modules\Reminders\Filament\Resources\ReminderResource\Pages\CreateReminder;
use App\Modules\Reminders\Filament\Resources\ReminderResource\Pages\ListReminders;
use App\Modules\Reminders\Models\Reminder;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('app'));
});

it('creates a reminder through the resource, stamping household and creator', function () {
    [$household, $owner] = makeHousehold();
    test()->actingAs($owner->user);
    Filament::setTenant($household);

    Livewire::test(CreateReminder::class)
        ->fillForm([
            'title' => 'Produžiti registraciju',
            'due_date' => now()->addDays(3)->format('Y-m-d H:i:s'),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $reminder = Reminder::firstWhere('title', 'Produžiti registraciju');

    expect($reminder)->not->toBeNull();
    expect($reminder->household_id)->toBe($household->id);
    expect($reminder->created_by)->toBe($owner->user_id);
});

it('never shows a reminder to a member of another household', function () {
    [$householdA, $ownerA] = makeHousehold();
    [$householdB, $ownerB] = makeHousehold();

    $reminder = Reminder::create([
        'household_id' => $householdA->id,
        'created_by' => $ownerA->user_id,
        'title' => 'Tajni podsjetnik A',
        'due_date' => now()->addDay(),
    ]);

    test()->actingAs($ownerB->user);
    Filament::setTenant($householdB);

    Livewire::test(ListReminders::class)->assertCanNotSeeTableRecords([$reminder]);
});
