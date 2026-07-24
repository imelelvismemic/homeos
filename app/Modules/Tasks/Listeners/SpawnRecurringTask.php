<?php

namespace App\Modules\Tasks\Listeners;

use App\Modules\Tasks\Enums\TaskStatus;
use App\Modules\Tasks\Events\TaskCompleted;
use App\Modules\Tasks\Models\Task;
use App\Modules\Tasks\Services\RecurrenceService;

/**
 * Auto-discoveran listener (CLAUDE.md §9): kad se ponavljajući zadatak završi,
 * kreira sljedeću instancu s pomjerenim rokom. Odluka iz Faze 3: "sljedeća
 * instanca na završetak", bez materijalizacije budućih instanci.
 */
class SpawnRecurringTask
{
    public function __construct(private RecurrenceService $recurrence) {}

    public function handle(TaskCompleted $event): void
    {
        $task = $event->task;

        if (! $task->isRecurring() || $task->due_date === null) {
            return;
        }

        $next = $this->recurrence->nextDueDate($task->recurrence_rule, $task->due_date);

        if ($next === null) {
            return;
        }

        $new = Task::create([
            'household_id' => $task->household_id,
            'created_by' => $task->created_by,
            'assigned_to' => $task->assigned_to,
            'title' => $task->title,
            'description' => $task->description,
            'priority' => $task->priority,
            'status' => TaskStatus::Todo,
            'due_date' => $next,
            'board_id' => $task->board_id,
            'recurrence_rule' => $task->recurrence_rule,
        ]);

        $new->syncTags($task->tagNames());
    }
}
