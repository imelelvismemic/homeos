<?php

use App\Modules\Tasks\Enums\Priority;
use App\Modules\Tasks\Enums\TaskStatus;
use App\Modules\Tasks\Filament\Resources\TaskResource;
use App\Modules\Tasks\Models\Task;
use Filament\Facades\Filament;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('app'));
});

it('finds tasks via Filament global search by title/description', function () {
    [$household, $owner] = makeHousehold();
    test()->actingAs($owner->user);
    Filament::setTenant($household);

    Task::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'title' => 'Rezervisati godišnji odmor',
        'priority' => Priority::Medium,
        'status' => TaskStatus::Todo,
    ]);

    $results = TaskResource::getGlobalSearchResults('godišnji');

    expect($results)->toHaveCount(1);
    expect($results->first()->title)->toBe('Rezervisati godišnji odmor');
});

it('does not leak another household\'s tasks through global search', function () {
    [$householdA, $ownerA] = makeHousehold();
    [$householdB, $ownerB] = makeHousehold();

    Task::create([
        'household_id' => $householdA->id,
        'created_by' => $ownerA->user_id,
        'title' => 'Tajni zadatak A',
        'priority' => Priority::Medium,
        'status' => TaskStatus::Todo,
    ]);

    test()->actingAs($ownerB->user);
    Filament::setTenant($householdB);

    expect(TaskResource::getGlobalSearchResults('Tajni'))->toHaveCount(0);
});
