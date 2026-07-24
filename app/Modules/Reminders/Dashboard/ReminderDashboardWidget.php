<?php

namespace App\Modules\Reminders\Dashboard;

use App\Modules\Reminders\Filament\Widgets\TodayRemindersWidget;
use App\Modules\Reminders\Models\Reminder;
use App\Platform\Contracts\DashboardWidgetContract;
use App\Platform\Models\Household;
use Illuminate\Database\Eloquent\Builder;

/**
 * Prijava Podsjetnika na "Today" dashboard (CLAUDE.md §7).
 */
class ReminderDashboardWidget implements DashboardWidgetContract
{
    public function title(): string
    {
        return __('reminders.plural_label');
    }

    public function widgetClass(): string
    {
        return TodayRemindersWidget::class;
    }

    public function hasContentFor(Household $household): bool
    {
        return static::relevantQuery($household)->exists();
    }

    /** Neokinuti podsjetnici s vremenom danas ili ranije. */
    public static function relevantQuery(Household $household): Builder
    {
        return Reminder::query()
            ->where('household_id', $household->id)
            ->visibleTo(auth()->user())
            ->whereNull('completed_at')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<=', today());
    }
}
