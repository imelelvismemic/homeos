<?php

use App\Modules\Tasks\Enums\Priority;
use App\Modules\Tasks\Enums\TaskStatus;
use App\Modules\Tasks\Filament\Resources\TaskResource\Pages\CreateTask;
use App\Modules\Tasks\Filament\Resources\TaskResource\Pages\EditTask;
use App\Modules\Tasks\Filament\Resources\TaskResource\Pages\ListTasks;
use App\Modules\Tasks\Models\Task;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('app'));
});

it('creates a task through the Filament resource, stamping household and creator', function () {
    [$household, $owner] = makeHousehold();
    test()->actingAs($owner->user);
    Filament::setTenant($household);

    Livewire::test(CreateTask::class)
        ->fillForm([
            'title' => 'Zaliti cvijeće',
            'priority' => Priority::Medium->value,
            'status' => TaskStatus::Todo->value,
            'tags' => ['balkon'],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $task = Task::firstWhere('title', 'Zaliti cvijeće');

    expect($task)->not->toBeNull();
    expect($task->household_id)->toBe($household->id);          // Filament tenancy
    expect($task->created_by)->toBe($owner->user_id);           // stamped by page
    expect($task->tagNames())->toContain('balkon');
});

it('edits a task through the Filament resource', function () {
    [$household, $owner] = makeHousehold();
    test()->actingAs($owner->user);
    Filament::setTenant($household);

    $task = Task::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'title' => 'Stari naslov',
        'priority' => Priority::Low,
        'status' => TaskStatus::Todo,
    ]);

    Livewire::test(EditTask::class, ['record' => $task->getRouteKey()])
        ->fillForm(['title' => 'Novi naslov', 'status' => TaskStatus::InProgress->value])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($task->fresh()->title)->toBe('Novi naslov');
    expect($task->fresh()->status)->toBe(TaskStatus::InProgress);
});

it('never shows a task to a member of another household', function () {
    [$householdA, $ownerA] = makeHousehold();
    [$householdB, $ownerB] = makeHousehold();

    $task = Task::create([
        'household_id' => $householdA->id,
        'created_by' => $ownerA->user_id,
        'title' => 'Tajni zadatak domaćinstva A',
        'priority' => Priority::Medium,
        'status' => TaskStatus::Todo,
    ]);

    // Član domaćinstva B gleda svoju listu — ne smije vidjeti tuđi zadatak.
    test()->actingAs($ownerB->user);
    Filament::setTenant($householdB);

    Livewire::test(ListTasks::class)
        ->assertCanNotSeeTableRecords([$task]);
});

it('hides a private task from other members of the same household', function () {
    [$household, $owner, $members] = makeHousehold(extraMembers: 1);
    $other = $members[0];

    $shared = Task::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'title' => 'Dijeljeni zadatak',
        'priority' => Priority::Medium,
        'status' => TaskStatus::Todo,
    ]);

    $private = Task::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'title' => 'Privatni zadatak',
        'priority' => Priority::Medium,
        'status' => TaskStatus::Todo,
    ]);
    $private->makePrivate();

    test()->actingAs($other->user);
    Filament::setTenant($household);

    Livewire::test(ListTasks::class)
        ->assertCanSeeTableRecords([$shared])
        ->assertCanNotSeeTableRecords([$private]);
});
