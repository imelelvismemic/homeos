<?php

namespace App\Modules\Notes\Calendar;

use App\Modules\Notes\Filament\Resources\NoteResource;
use App\Modules\Notes\Models\Note;
use App\Platform\Calendar\CalendarEvent;
use App\Platform\Contracts\CalendarSourceContract;
use App\Platform\Models\Household;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Unosi dnevnika (bilješka s `journal_date`) na kalendaru — time datum dnevnika
 * dobija stvarnu svrhu: dan u kalendaru pokazuje šta je tog dana zapisano
 * (ORIGINAL_SPEC "Prostor za dnevne bilješke / dnevnik").
 *
 * Obične bilješke (bez datuma dnevnika) se NE prikazuju — kalendar je pregled
 * onoga što je vezano za dan, ne spisak svega.
 */
class JournalCalendarSource implements CalendarSourceContract
{
    public function eventsBetween(CarbonInterface $start, CarbonInterface $end, Household $household): Collection
    {
        return Note::query()
            ->where('household_id', $household->id)
            ->visibleTo(auth()->user())
            ->whereNotNull('journal_date')
            ->whereBetween('journal_date', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->get()
            ->map(fn (Note $note) => new CalendarEvent(
                type: 'journal',
                id: $note->id,
                title: $note->displayTitle(),
                start: $note->journal_date->toDateString(),
                url: NoteResource::getUrl('edit', ['record' => $note, 'tenant' => $household]),
                color: '#3E7C8C',
                allDay: true,
            ));
    }

    public function type(): string
    {
        return 'journal';
    }
}
