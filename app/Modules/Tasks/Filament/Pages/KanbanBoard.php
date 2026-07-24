<?php

namespace App\Modules\Tasks\Filament\Pages;

use App\Modules\Tasks\Enums\TaskStatus;
use App\Modules\Tasks\Models\Board;
use App\Modules\Tasks\Models\Task;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

/**
 * Kanban tabla (ROADMAP Faza 3, CLAUDE.md §5). Kolone su statusi zadatka;
 * opcioni filter po tabli (tasks_boards). Prevlačenje kartice mijenja
 * Task.status; touch-friendly fallback je padajući izbornik na svakoj kartici
 * (CLAUDE.md §6 — Kanban mora imati alternativu drag&drop-u na mobilnom).
 *
 * Radi nad istim Task modelom/podacima kao Resource, kalendar i dashboard —
 * ne uvodi zaseban "kanban" entitet.
 */
class KanbanBoard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-view-columns';

    protected static string $view = 'filament.tasks.pages.kanban-board';

    protected static ?int $navigationSort = 2;

    public ?int $boardId = null;

    public static function getNavigationLabel(): string
    {
        return __('tasks.kanban.title');
    }

    public function getTitle(): string
    {
        return __('tasks.kanban.title');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('tasks.navigation_group');
    }

    /**
     * @return array<int|string, string>
     */
    public function boardOptions(): array
    {
        return Board::query()
            ->where('household_id', Filament::getTenant()?->id)
            ->orderBy('position')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    /**
     * @return array<int, TaskStatus>
     */
    public function statuses(): array
    {
        return TaskStatus::cases();
    }

    /**
     * @return Collection<int, Task>
     */
    public function tasksFor(TaskStatus $status): Collection
    {
        return Task::query()
            ->where('household_id', Filament::getTenant()?->id)
            ->visibleTo(auth()->user())
            ->where('status', $status->value)
            ->when($this->boardId, fn ($q) => $q->where('board_id', $this->boardId))
            ->orderBy('position')
            ->orderBy('due_date')
            ->get();
    }

    /** Premjesti karticu u drugi status (drag&drop ili padajući izbornik). */
    public function moveTask(int|string $taskId, string $status): void
    {
        $target = TaskStatus::tryFrom($status);

        if ($target === null) {
            return;
        }

        $task = Task::query()
            ->where('household_id', Filament::getTenant()?->id)
            ->whereKey($taskId)
            ->first();

        // Autorizacija ide kroz Policy — nikad ručna provjera (CLAUDE.md §11).
        if ($task === null || ! auth()->user()->can('update', $task)) {
            return;
        }

        if ($task->status !== $target) {
            $task->update(['status' => $target]);
        }
    }
}
