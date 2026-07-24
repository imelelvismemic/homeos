<?php

use App\Modules\Notes\Filament\Resources\NoteResource\Pages\CreateNote;
use App\Modules\Notes\Filament\Resources\NoteResource\Pages\ListNotes;
use App\Modules\Notes\Models\Note;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('app'));
});

it('creates a note through the resource with tags, stamping household and creator', function () {
    [$household, $owner] = makeHousehold();
    test()->actingAs($owner->user);
    Filament::setTenant($household);

    Livewire::test(CreateNote::class)
        ->fillForm([
            'title' => 'Plan za vikend',
            'body' => '<p>Izlet u prirodu</p>',
            'tags' => ['porodica'],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $note = Note::firstWhere('title', 'Plan za vikend');

    expect($note)->not->toBeNull();
    expect($note->household_id)->toBe($household->id);
    expect($note->created_by)->toBe($owner->user_id);
    expect($note->tagNames())->toContain('porodica');
});

it('never shows a note to a member of another household', function () {
    [$householdA, $ownerA] = makeHousehold();
    [$householdB, $ownerB] = makeHousehold();

    $note = Note::create([
        'household_id' => $householdA->id,
        'created_by' => $ownerA->user_id,
        'body' => '<p>Tajna bilješka A</p>',
    ]);

    test()->actingAs($ownerB->user);
    Filament::setTenant($householdB);

    Livewire::test(ListNotes::class)->assertCanNotSeeTableRecords([$note]);
});
