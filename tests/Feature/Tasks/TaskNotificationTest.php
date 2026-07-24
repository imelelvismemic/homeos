<?php

use App\Modules\Tasks\Enums\Priority;
use App\Modules\Tasks\Enums\TaskStatus;
use App\Modules\Tasks\Models\Task;
use App\Modules\Tasks\Notifications\TaskAssigned;
use App\Modules\Tasks\Notifications\TaskDueSoon;
use Illuminate\Support\Facades\Notification;

it('notifies the assignee when a task is assigned to them', function () {
    [$household, $owner, $members] = makeHousehold(extraMembers: 1);
    $assignee = $members[0];
    Notification::fake();

    Task::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'title' => 'Odnijeti smeće',
        'priority' => Priority::Medium,
        'status' => TaskStatus::Todo,
        'assigned_to' => $assignee->id,
    ]);

    Notification::assertSentTo($assignee, TaskAssigned::class);
});

it('notifies assignees of tasks due within 24 hours via the scheduler command', function () {
    [$household, $owner, $members] = makeHousehold(extraMembers: 1);
    $assignee = $members[0];
    Notification::fake();

    // Ističe za 12h — u prozoru.
    $soon = Task::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'title' => 'Predati izvještaj',
        'priority' => Priority::High,
        'status' => TaskStatus::Todo,
        'assigned_to' => $assignee->id,
        'due_date' => now()->addHours(12),
    ]);

    // Ističe za 5 dana — van prozora, ne smije obavijestiti.
    Task::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'title' => 'Daleki rok',
        'priority' => Priority::Low,
        'status' => TaskStatus::Todo,
        'assigned_to' => $assignee->id,
        'due_date' => now()->addDays(5),
    ]);

    test()->artisan('tasks:notify-due-soon')->assertSuccessful();

    Notification::assertSentTo(
        $assignee,
        TaskDueSoon::class,
        fn (TaskDueSoon $n) => $n->task->is($soon),
    );
    Notification::assertSentToTimes($assignee, TaskDueSoon::class, 1);
});
