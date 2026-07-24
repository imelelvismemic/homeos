<?php

use App\Modules\Notes\Events\NoteCreated;
use App\Modules\Notes\Models\Note;
use App\Platform\Enums\Visibility;
use Illuminate\Support\Facades\Event;

function makeNote(array $attributes = []): Note
{
    [$household, $owner] = $attributes['_ctx'] ?? makeHousehold();
    unset($attributes['_ctx']);

    return Note::create(array_merge([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'body' => '<p>Sadržaj bilješke</p>',
    ], $attributes));
}

it('creates a note with a household-visible share and dispatches NoteCreated', function () {
    Event::fake([NoteCreated::class]);

    $note = makeNote(['title' => 'Ideja']);

    expect($note->exists)->toBeTrue();
    expect($note->share->visibility)->toBe(Visibility::Household);
    Event::assertDispatched(NoteCreated::class, fn ($e) => $e->note->is($note));
});

it('falls back to a body excerpt when there is no title', function () {
    $note = makeNote(['title' => null, 'body' => '<p>Ovo je <strong>važna</strong> bilješka bez naslova</p>']);

    expect($note->displayTitle())->toBe('Ovo je važna bilješka bez naslova');
});

it('supports tags scoped to the household and a journal date', function () {
    $note = makeNote(['journal_date' => '2026-07-24']);
    $note->tag(['dnevnik', 'porodica']);

    expect($note->journal_date->toDateString())->toBe('2026-07-24');
    expect($note->tagNames())->toContain('dnevnik')->toContain('porodica');
    expect($note->tags->first()->household_id)->toBe($note->household_id);
});
