<?php

use App\Modules\Reminders\Events\ReminderFired;
use App\Modules\Reminders\Filament\Resources\ReminderResource\Pages\EditReminder;
use App\Modules\Reminders\Filament\Widgets\TodayRemindersWidget;
use App\Modules\Reminders\Models\Reminder;
use App\Modules\Reminders\Notifications\ReminderDue;
use App\Modules\Reminders\Services\ReminderFirer;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('app'));
});

it('does not re-fire a reminder whose notification delivery failed (no repeat every minute)', function () {
    [$household, $owner] = makeHousehold();

    $reminder = Reminder::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'title' => 'Prošlo vrijeme',
        'due_date' => now()->subMinutes(10),
    ]);

    Notification::fake();

    // Obrada eventa pukne (npr. mail provider nedostupan) — podsjetnik ipak MORA
    // ostati označen okinutim, inače ga scheduler ponavlja (i obavještava) svake minute.
    Event::listen(ReminderFired::class, function (): void {
        throw new RuntimeException('mail down');
    });

    test()->artisan('reminders:fire')->assertSuccessful();

    expect($reminder->fresh()->completed_at)->not->toBeNull();

    // Drugi prolaz ne smije više pokupiti isti podsjetnik.
    test()->artisan('reminders:fire')->assertSuccessful()->expectsOutput('Okinuto podsjetnika: 0');
});

it('fires a reminder only once through the service', function () {
    [$household, $owner] = makeHousehold();
    Notification::fake();

    $reminder = Reminder::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'title' => 'Jednokratni',
        'due_date' => now()->subMinute(),
    ]);

    $firer = app(ReminderFirer::class);

    expect($firer->fire($reminder))->toBeTrue();
    expect($firer->fire($reminder->fresh()))->toBeFalse();

    Notification::assertSentToTimes($owner, ReminderDue::class, 1);
});

it('sends the notification when a reminder is fired manually from the edit page', function () {
    [$household, $owner] = makeHousehold();
    test()->actingAs($owner->user);
    Filament::setTenant($household);
    Notification::fake();

    $reminder = Reminder::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'title' => 'Ručno okinut',
        'due_date' => now()->addDay(),
    ]);

    Livewire::test(EditReminder::class, ['record' => $reminder->getKey()])
        ->callAction('complete');

    expect($reminder->fresh()->completed_at)->not->toBeNull();
    Notification::assertSentTo($owner, ReminderDue::class);
});

it('fires a reminder straight from the dashboard widget', function () {
    [$household, $owner] = makeHousehold();
    test()->actingAs($owner->user);
    Filament::setTenant($household);
    Notification::fake();

    $reminder = Reminder::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'title' => 'S dashboarda',
        'due_date' => now()->subHour(),
    ]);

    Livewire::test(TodayRemindersWidget::class)
        ->call('fireReminder', $reminder->getKey());

    expect($reminder->fresh()->completed_at)->not->toBeNull();
    Notification::assertSentTo($owner, ReminderDue::class);
});

it('builds the reminder email in scheduler context, where there is no tenant', function () {
    [$household, $owner] = makeHousehold();

    $reminder = Reminder::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'title' => 'Podsjetnik iz schedulera',
        'due_date' => now()->subMinute(),
    ]);

    // Scheduler radi u konzoli: panel postoji, ali TENANT nije postavljen.
    Filament::setTenant(null);

    $mail = (new ReminderDue($reminder))->toMail($owner);

    // Bez eksplicitnog tenanta getUrl() puca na nedostajućem {tenant} parametru,
    // pa cijeli email padne — in-app obavijest stigne, email nikad.
    expect($mail->actionUrl)->toContain((string) $household->id);
});
