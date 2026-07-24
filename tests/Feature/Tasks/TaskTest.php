<?php

use App\Modules\Tasks\Enums\Priority;
use App\Modules\Tasks\Enums\TaskStatus;
use App\Modules\Tasks\Events\TaskAssigned;
use App\Modules\Tasks\Events\TaskCompleted;
use App\Modules\Tasks\Events\TaskCreated;
use App\Modules\Tasks\Events\TaskDueDateChanged;
use App\Modules\Tasks\Models\Task;
use App\Platform\Enums\Visibility;
use Illuminate\Support\Facades\Event;

function makeTask(array $attributes = []): Task
{
    [$household, $owner] = $attributes['_context'] ?? makeHousehold();
    unset($attributes['_context']);

    return Task::create(array_merge([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'title' => 'Oprati suđe',
        'priority' => Priority::Medium,
        'status' => TaskStatus::Todo,
    ], $attributes));
}

it('creates a task with sane defaults and a household-visible share', function () {
    $task = makeTask();

    expect($task->exists)->toBeTrue();
    expect($task->status)->toBe(TaskStatus::Todo);
    expect($task->completed_at)->toBeNull();
    // Shareable: podrazumijevano vidljivo cijelom domaćinstvu (CLAUDE.md §11).
    expect($task->share->visibility)->toBe(Visibility::Household);
});

it('syncs completed_at when status becomes done and clears it when reopened', function () {
    $task = makeTask();

    $task->update(['status' => TaskStatus::Done]);
    expect($task->completed_at)->not->toBeNull();

    $task->update(['status' => TaskStatus::Todo]);
    expect($task->fresh()->completed_at)->toBeNull();
});

it('dispatches TaskCreated on creation', function () {
    Event::fake([TaskCreated::class, TaskDueDateChanged::class]);

    $task = makeTask(['due_date' => now()->addDay()]);

    Event::assertDispatched(TaskCreated::class, fn ($e) => $e->task->is($task));
    Event::assertDispatched(TaskDueDateChanged::class, fn ($e) => $e->task->is($task));
});

it('dispatches TaskCompleted when status changes to done', function () {
    Event::fake([TaskCompleted::class]);
    $task = makeTask();

    $task->update(['status' => TaskStatus::Done]);

    Event::assertDispatched(TaskCompleted::class, fn ($e) => $e->task->is($task));
});

it('dispatches TaskDueDateChanged when the due date changes', function () {
    $task = makeTask();
    Event::fake([TaskDueDateChanged::class]);

    $task->update(['due_date' => now()->addWeek()]);

    Event::assertDispatched(TaskDueDateChanged::class, fn ($e) => $e->task->is($task));
});

it('dispatches TaskAssigned when an assignee is set', function () {
    [$household, $owner, $members] = makeHousehold(extraMembers: 1);
    Event::fake([TaskAssigned::class]);

    $task = makeTask([
        '_context' => [$household, $owner],
        'assigned_to' => $members[0]->id,
    ]);

    Event::assertDispatched(TaskAssigned::class, fn ($e) => $e->task->is($task));
});

it('spawns the next instance when a recurring task is completed', function () {
    $due = now()->startOfDay()->addDays(2);
    $task = makeTask(['due_date' => $due, 'recurrence_rule' => 'FREQ=WEEKLY']);
    $task->tag(['kućni poslovi']);

    $task->update(['status' => TaskStatus::Done]);

    $next = Task::query()->where('id', '!=', $task->id)->latest('id')->first();

    expect($next)->not->toBeNull();
    expect($next->status)->toBe(TaskStatus::Todo);
    expect($next->due_date->toDateString())->toBe($due->copy()->addWeek()->toDateString());
    expect($next->tagNames())->toContain('kućni poslovi');
});

it('does not spawn a next instance for a non-recurring task', function () {
    $task = makeTask(['due_date' => now()->addDay()]);

    $task->update(['status' => TaskStatus::Done]);

    expect(Task::count())->toBe(1);
});

it('scopes tags to the household', function () {
    [$householdA, $ownerA] = makeHousehold();
    [$householdB, $ownerB] = makeHousehold();

    $a = makeTask(['_context' => [$householdA, $ownerA]]);
    $b = makeTask(['_context' => [$householdB, $ownerB]]);

    $a->tag(['hitno']);
    $b->tag(['hitno']);

    // Ista oznaka po nazivu, ali odvojeni zapisi po domaćinstvu.
    expect($a->tags->first()->household_id)->toBe($householdA->id);
    expect($b->tags->first()->household_id)->toBe($householdB->id);
    expect($a->tags->first()->id)->not->toBe($b->tags->first()->id);
});
