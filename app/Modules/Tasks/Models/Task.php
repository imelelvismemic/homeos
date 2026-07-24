<?php

namespace App\Modules\Tasks\Models;

use App\Models\User;
use App\Modules\Tasks\Enums\Priority;
use App\Modules\Tasks\Enums\TaskStatus;
use App\Modules\Tasks\Events\TaskAssigned;
use App\Modules\Tasks\Events\TaskCompleted;
use App\Modules\Tasks\Events\TaskCreated;
use App\Modules\Tasks\Events\TaskDueDateChanged;
use App\Platform\Concerns\Shareable;
use App\Platform\Concerns\Taggable;
use App\Platform\Models\Household;
use App\Platform\Models\HouseholdMember;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Zadatak (DATA_MODEL.md §4). Referentni "modul entitet": Shareable (dijeljenje/
 * privatnost), Taggable (oznake), household_id + created_by, emituje evente.
 */
#[Fillable([
    'household_id', 'created_by', 'assigned_to', 'title', 'description',
    'priority', 'status', 'due_date', 'completed_at', 'parent_task_id',
    'board_id', 'recurrence_rule', 'position',
])]
class Task extends Model
{
    use HasFactory;
    use Shareable;
    use Taggable;

    protected function casts(): array
    {
        return [
            'priority' => Priority::class,
            'status' => TaskStatus::class,
            'due_date' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        // Invarijanta: status i completed_at su usklađeni bez obzira ko mijenja
        // status (forma, Kanban, servis).
        static::saving(function (Task $task) {
            if ($task->status === TaskStatus::Done && $task->completed_at === null) {
                $task->completed_at = now();
            } elseif ($task->status !== TaskStatus::Done) {
                $task->completed_at = null;
            }
        });

        static::created(function (Task $task) {
            TaskCreated::dispatch($task);

            if ($task->due_date !== null) {
                TaskDueDateChanged::dispatch($task);
            }

            if ($task->assigned_to !== null) {
                TaskAssigned::dispatch($task);
            }
        });

        static::updated(function (Task $task) {
            if ($task->wasChanged('due_date')) {
                TaskDueDateChanged::dispatch($task);
            }

            // Prelazak na "done" → TaskCompleted (listener rješava ponavljanje).
            if ($task->wasChanged('status') && $task->status === TaskStatus::Done) {
                TaskCompleted::dispatch($task);
            }

            // Nova/promijenjena odgovorna osoba → obavijesti je.
            if ($task->wasChanged('assigned_to') && $task->assigned_to !== null) {
                TaskAssigned::dispatch($task);
            }
        });
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(HouseholdMember::class, 'assigned_to');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_task_id');
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_task_id');
    }

    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class, 'board_id');
    }

    public function isRecurring(): bool
    {
        return filled($this->recurrence_rule);
    }
}
