<?php

namespace App\Modules\Tasks\Filament\Widgets;

use App\Modules\Tasks\Dashboard\TaskDashboardWidget;
use App\Modules\Tasks\Models\Task;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class TodayTasksWidget extends Widget
{
    protected static string $view = 'filament.tasks.widgets.today-tasks';

    protected int|string|array $columnSpan = 'full';

    /**
     * @return Collection<int, Task>
     */
    public function getTasks(): Collection
    {
        $household = Filament::getTenant();

        if ($household === null) {
            return collect();
        }

        return TaskDashboardWidget::relevantQuery($household)
            ->orderBy('due_date')
            ->limit(8)
            ->get();
    }
}
