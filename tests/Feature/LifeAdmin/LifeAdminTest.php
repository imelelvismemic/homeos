<?php

use App\Modules\LifeAdmin\Enums\DocumentType;
use App\Modules\LifeAdmin\Events\DocumentCreated;
use App\Modules\LifeAdmin\Models\Contact;
use App\Modules\LifeAdmin\Models\Document;
use App\Modules\Reminders\Models\Reminder;
use App\Platform\Enums\Visibility;
use Illuminate\Support\Facades\Event;

it('creates a document with a household-visible share and dispatches DocumentCreated', function () {
    [$household, $owner] = makeHousehold();
    Event::fake([DocumentCreated::class]);

    $document = Document::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'type' => DocumentType::IdDocument,
        'title' => 'Pasoš',
        'expiry_date' => now()->addYears(2)->toDateString(),
    ]);

    expect($document->exists)->toBeTrue();
    expect($document->share->visibility)->toBe(Visibility::Household);
    Event::assertDispatched(DocumentCreated::class, fn ($e) => $e->document->is($document));
});

it('automatically requests an expiry reminder when a document with an expiry date is created (DoD)', function () {
    [$household, $owner] = makeHousehold();

    $document = Document::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'type' => DocumentType::Renewal,
        'title' => 'Registracija auta',
        'expiry_date' => now()->addDays(40)->toDateString(),
        'remind_days_before' => 30,
    ]);

    // DocumentCreated → RequestDocumentExpiryReminder → ReminderRequested → Reminder
    $reminder = Reminder::query()
        ->where('remindable_type', $document->getMorphClass())
        ->where('remindable_id', $document->id)
        ->first();

    expect($reminder)->not->toBeNull();
    expect($reminder->household_id)->toBe($household->id);
    expect($reminder->due_date->toDateString())->toBe(now()->addDays(10)->toDateString()); // 40 − 30
});

it('does not request a reminder for a document without an expiry date', function () {
    [$household, $owner] = makeHousehold();

    $document = Document::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'type' => DocumentType::Other,
        'title' => 'Uputstvo za mašinu',
    ]);

    $reminder = Reminder::query()
        ->where('remindable_type', $document->getMorphClass())
        ->where('remindable_id', $document->id)
        ->first();

    expect($reminder)->toBeNull();
});

it('hides a private document from other members of the same household', function () {
    [$household, $owner, $members] = makeHousehold(extraMembers: 1);
    $other = $members[0];

    $document = Document::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'type' => DocumentType::IdDocument,
        'title' => 'Privatni ugovor',
        'expiry_date' => now()->addYear()->toDateString(),
    ]);
    $document->makePrivate();

    expect($document->isVisibleTo($owner->user))->toBeTrue();
    expect($document->isVisibleTo($other->user))->toBeFalse();
});

it('does not leak a document to a member of another household', function () {
    [$householdA, $ownerA] = makeHousehold();
    [$householdB, $ownerB] = makeHousehold();

    $document = Document::create([
        'household_id' => $householdA->id,
        'created_by' => $ownerA->user_id,
        'type' => DocumentType::Contract,
        'title' => 'Ugovor A',
        'expiry_date' => now()->addYear()->toDateString(),
    ]);

    expect($document->isVisibleTo($ownerB->user))->toBeFalse();
});

it('creates a contact without an expiry reminder', function () {
    [$household, $owner] = makeHousehold();

    $contact = Contact::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'name' => 'Vodoinstalater Meho',
        'relationship' => 'vodoinstalater',
        'phone' => '061123456',
    ]);

    expect($contact->exists)->toBeTrue();
    expect($contact->share->visibility)->toBe(Visibility::Household);
});
