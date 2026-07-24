<?php

use App\Modules\Tasks\Enums\Priority;
use App\Modules\Tasks\Enums\TaskStatus;
use App\Modules\Tasks\Models\Task;
use Filament\Facades\Filament;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('app'));
});

function searchUrl($household, string $q): string
{
    return route('filament.app.search', ['h' => $household->getKey()]).'&q='.urlencode($q);
}

it('returns grouped JSON results for the household', function () {
    [$household, $owner] = makeHousehold();

    Task::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'title' => 'Rezervisati godišnji odmor',
        'priority' => Priority::Medium,
        'status' => TaskStatus::Todo,
    ]);

    test()->actingAs($owner->user)
        ->getJson(searchUrl($household, 'godišnji'))
        ->assertOk()
        ->assertJsonPath('groups.0.type', 'task')
        ->assertJsonPath('groups.0.label', 'Zadaci')
        ->assertJsonPath('groups.0.results.0.title', 'Rezervisati godišnji odmor');
});

it('returns empty for queries shorter than two characters', function () {
    [$household, $owner] = makeHousehold();

    test()->actingAs($owner->user)
        ->getJson(searchUrl($household, 'a'))
        ->assertOk()
        ->assertExactJson(['groups' => []]);
});

it('rejects unauthenticated requests', function () {
    [$household] = makeHousehold();

    test()->getJson(searchUrl($household, 'nešto'))->assertStatus(403);
});

it('does not leak another household\'s tasks (membership enforced)', function () {
    [$householdA, $ownerA] = makeHousehold();
    [, $ownerB] = makeHousehold();

    Task::create([
        'household_id' => $householdA->id,
        'created_by' => $ownerA->user_id,
        'title' => 'Tajni zadatak A',
        'priority' => Priority::Medium,
        'status' => TaskStatus::Todo,
    ]);

    // Član B traži rezultate domaćinstva A — nije član → 404.
    test()->actingAs($ownerB->user)
        ->getJson(searchUrl($householdA, 'Tajni'))
        ->assertStatus(404);
});
