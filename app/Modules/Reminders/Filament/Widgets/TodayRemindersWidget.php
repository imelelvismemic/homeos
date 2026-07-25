<?php

namespace App\Modules\Reminders\Filament\Widgets;

use App\Modules\Reminders\Dashboard\ReminderDashboardWidget;
use App\Modules\Reminders\Models\Reminder;
use App\Modules\Reminders\Services\ReminderFirer;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
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

    /**
     * Okidanje podsjetnika direktno s dashboarda — isti servis kao scheduler i
     * lista podsjetnika, pa i ovdje ide obavještenje odgovornoj osobi.
     */
    public function fireReminder(int $id): void
    {
        $household = Filament::getTenant();

        if ($household === null) {
            return;
        }

        $reminder = ReminderDashboardWidget::relevantQuery($household)->whereKey($id)->first();

        if ($reminder === null) {
            return;
        }

        app(ReminderFirer::class)->fire($reminder);

        Notification::make()
            ->title(__('reminders.actions.completed_notice'))
            ->success()
            ->send();
    }
}
