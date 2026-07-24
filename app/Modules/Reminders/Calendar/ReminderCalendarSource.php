<?php

namespace App\Modules\Reminders\Calendar;

use App\Modules\Reminders\Filament\Resources\ReminderResource;
use App\Modules\Reminders\Models\Reminder;
use App\Platform\Calendar\CalendarEvent;
use App\Platform\Contracts\CalendarSourceContract;
use App\Platform\Models\Household;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Izlaže podsjetnike s vremenom kalendaru (DATA_MODEL.md §10). Isti obrazac kao
 * TaskCalendarSource — podsjetnik se pojavi na kalendaru bez da Kalendar zna za
 * Podsjetnike.
 */
class ReminderCalendarSource implements CalendarSourceContract
{
    public function eventsBetween(CarbonInterface $start, CarbonInterface $end, Household $household): Collection
    {
        return Reminder::query()
            ->where('household_id', $household->id)
            ->visibleTo(auth()->user())
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$start, $end])
            ->get()
            ->map(fn (Reminder $reminder) => new CalendarEvent(
                type: 'reminder',
                id: $reminder->id,
                title: $reminder->title,
                start: $reminder->due_date->toIso8601String(),
                url: ReminderResource::getUrl('edit', ['record' => $reminder, 'tenant' => $household]),
                color: $reminder->completed_at ? '#5E8C6A' : '#D99A3C',
            ));
    }

    public function type(): string
    {
        return 'reminder';
    }
}
