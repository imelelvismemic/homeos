<?php

namespace App\Modules\Tasks\Events;

use App\Modules\Tasks\Models\Task;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Rok zadatka je postavljen ili promijenjen. Kalendar/Podsjetnici mogu reagovati
 * (CLAUDE.md §9) — Zadaci ne znaju za njih.
 */
class TaskDueDateChanged
{
    use Dispatchable;

    public function __construct(public Task $task) {}
}
