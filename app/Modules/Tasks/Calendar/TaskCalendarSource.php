<?php

namespace App\Modules\Tasks\Calendar;

use App\Modules\Tasks\Enums\Priority;
use App\Modules\Tasks\Enums\TaskStatus;
use App\Modules\Tasks\Filament\Resources\TaskResource;
use App\Modules\Tasks\Models\Task;
use App\Platform\Calendar\CalendarEvent;
use App\Platform\Contracts\CalendarSourceContract;
use App\Platform\Models\Household;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Izlaže zadatke s rokom kalendaru (DATA_MODEL.md §10). Kalendar ovo agregira —
 * pa se zadatak s rokom AUTOMATSKI pojavi na kalendaru bez dupliranja podataka i
 * bez da Kalendar zna za Task (CLAUDE.md §9). Registrovano u config/homeos-apps.php.
 */
class TaskCalendarSource implements CalendarSourceContract
{
    public function eventsBetween(CarbonInterface $start, CarbonInterface $end, Household $household): Collection
    {
        return Task::query()
            ->where('household_id', $household->id)
            ->visibleTo(auth()->user())
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$start, $end])
            ->get()
            ->map(fn (Task $task) => new CalendarEvent(
                type: 'task',
                id: $task->id,
                title: $task->title,
                start: $task->due_date->toIso8601String(),
                url: TaskResource::getUrl('edit', ['record' => $task]),
                color: $this->color($task),
            ));
    }

    public function type(): string
    {
        return 'task';
    }

    private function color(Task $task): ?string
    {
        if ($task->status === TaskStatus::Done) {
            return '#5E8C6A'; // žalfija (završeno)
        }

        return $task->priority === Priority::High ? '#B23B2E' : '#BF6A44';
    }
}
