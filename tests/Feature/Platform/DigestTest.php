<?php

use App\Modules\Finance\Models\Bill;
use App\Modules\Tasks\Models\Task;
use App\Platform\Digest\DigestService;
use App\Platform\Enums\DigestFrequency;
use App\Platform\Notifications\DigestNotification;
use App\Platform\Notifications\NotificationCategoryRegistry;
use Illuminate\Support\Facades\Notification;

it('registers module notification categories plus the platform one', function () {
    $keys = NotificationCategoryRegistry::keys();

    expect($keys)->toContain('shared_with_you'); // platform
    expect($keys)->toContain('task_assigned', 'task_due_soon', 'reminder_fired', 'bill_due'); // moduli
});

it('aggregates upcoming items across modules into digest sections', function () {
    [$household, $owner] = makeHousehold();

    Task::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'title' => 'Platiti kiriju',
        'due_date' => now()->addHours(10),
    ]);

    Bill::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'title' => 'Struja',
        'amount' => 50,
        // Datumska kolona → sutrašnji dan (ponoć), uvijek unutar [sad, sad+1d].
        'due_date' => now()->addDay()->toDateString(),
    ]);

    $sections = app(DigestService::class)->sectionsFor($household, $owner->user, now(), now()->addDay());
    $lines = collect($sections)->flatMap(fn ($s) => $s->lines)->implode("\n");

    expect($lines)->toContain('Platiti kiriju'); // zadatak s rokom
    expect($lines)->toContain('Struja');         // račun pred dospijećem
});

it('keeps a private item out of another member\'s digest', function () {
    [$household, $owner, $members] = makeHousehold(extraMembers: 1);
    $other = $members[0];

    // Privatni zadatak (zadaci ne generišu podsjetnik → čist test vidljivosti).
    $private = Task::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'title' => 'Tajni zadatak',
        'due_date' => now()->addHours(10),
    ]);
    $private->makePrivate();

    $service = app(DigestService::class);

    $ownerLines = collect($service->sectionsFor($household, $owner->user, now(), now()->addDay()))
        ->flatMap(fn ($s) => $s->lines)->implode("\n");
    $otherLines = collect($service->sectionsFor($household, $other->user, now(), now()->addDay()))
        ->flatMap(fn ($s) => $s->lines)->implode("\n");

    expect($ownerLines)->toContain('Tajni zadatak');     // vlasnik vidi svoj privatni zadatak
    expect($otherLines)->not->toContain('Tajni zadatak'); // drugi član ne vidi
});

it('sends the digest only to members who chose that frequency and only when there is content', function () {
    Notification::fake();
    [$household, $owner, $members] = makeHousehold(extraMembers: 2);
    [$daily, $none] = $members;

    $daily->update(['digest_frequency' => DigestFrequency::Daily->value]);
    $none->update(['digest_frequency' => DigestFrequency::None->value]);
    $owner->update(['digest_frequency' => DigestFrequency::Daily->value]);

    Task::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'title' => 'Nešto za danas',
        'due_date' => now()->addHours(6),
    ]);

    app(DigestService::class)->sendDue(DigestFrequency::Daily, now());

    Notification::assertSentTo($daily, DigestNotification::class);
    Notification::assertSentTo($owner, DigestNotification::class);
    Notification::assertNotSentTo($none, DigestNotification::class); // nije odabrao dnevni
});

it('does not send an empty digest', function () {
    Notification::fake();
    [$household, $owner] = makeHousehold();
    $owner->update(['digest_frequency' => DigestFrequency::Daily->value]);

    // Nema stavki u periodu.
    app(DigestService::class)->sendDue(DigestFrequency::Daily, now());

    Notification::assertNothingSent();
});

it('sends the digest on the mail channel only', function () {
    $notification = new DigestNotification([], DigestFrequency::Daily);

    expect($notification->via(new stdClass))->toBe(['mail']);
});
