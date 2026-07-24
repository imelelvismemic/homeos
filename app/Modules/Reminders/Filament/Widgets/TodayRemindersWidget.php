<?php

namespace App\Modules\Reminders\Filament\Widgets;

use App\Modules\Reminders\Dashboard\ReminderDashboardWidget;
use App\Modules\Reminders\Models\Reminder;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class TodayRemindersWidget extends Widget
{
    protected static string $view = 'filament.reminders.widgets.today-reminders';

    protected int|string|array $columnSpan = 'full';

    /**
     * @return Collection<int, Reminder>
     */
    public function getReminders(): Collection
    {
        $household = Filament::getTenant();

        if ($household === null) {
            return collect();
        }

        return ReminderDashboardWidget::relevantQuery($household)
            ->orderBy('due_date')
            ->limit(8)
            ->get();
    }
}
