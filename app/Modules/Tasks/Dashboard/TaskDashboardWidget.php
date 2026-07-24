<?php

namespace App\Modules\Tasks\Dashboard;

use App\Modules\Tasks\Filament\Widgets\TodayTasksWidget;
use App\Modules\Tasks\Models\Task;
use App\Platform\Contracts\DashboardWidgetContract;
use App\Platform\Models\Household;
use Illuminate\Database\Eloquent\Builder;

/**
 * Prijava Zadataka na "Today" dashboard (CLAUDE.md §7). Registrovano u
 * config/homeos-apps.php pod `dashboard_widget`.
 */
class TaskDashboardWidget implements DashboardWidgetContract
{
    public function title(): string
    {
        return __('tasks.plural_label');
    }

    public function widgetClass(): string
    {
        return TodayTasksWidget::class;
    }

    public function hasContentFor(Household $household): bool
    {
        return static::relevantQuery($household)->exists();
    }

    /** Nezavršeni zadaci s rokom danas ili ranije (zakašnjeli). */
    public static function relevantQuery(Household $household): Builder
    {
        return Task::query()
            ->where('household_id', $household->id)
            ->visibleTo(auth()->user())
            ->whereNull('completed_at')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<=', today());
    }
}
