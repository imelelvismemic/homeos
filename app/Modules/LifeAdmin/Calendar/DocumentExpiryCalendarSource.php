<?php

namespace App\Modules\LifeAdmin\Calendar;

use App\Modules\LifeAdmin\Filament\Resources\DocumentResource;
use App\Modules\LifeAdmin\Models\Document;
use App\Platform\Calendar\CalendarEvent;
use App\Platform\Contracts\CalendarSourceContract;
use App\Platform\Models\Household;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Istekni datumi dokumenata na kalendaru (DATA_MODEL.md §4c) — isti obrazac kao
 * Task/Reminder/Bill. Kalendar ne zna za Life admin.
 */
class DocumentExpiryCalendarSource implements CalendarSourceContract
{
    public function eventsBetween(CarbonInterface $start, CarbonInterface $end, Household $household): Collection
    {
        return Document::query()
            ->where('household_id', $household->id)
            ->visibleTo(auth()->user())
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [$start, $end])
            ->get()
            ->map(fn (Document $document) => new CalendarEvent(
                type: 'document',
                id: $document->id,
                title: __('lifeadmin.calendar.expiry', ['title' => $document->title]),
                start: $document->expiry_date->toIso8601String(),
                url: DocumentResource::getUrl('edit', ['record' => $document, 'tenant' => $household]),
                color: $document->expiry_date->isPast() ? '#B23B2E' : '#D99A3C',
                allDay: true,
            ));
    }

    public function type(): string
    {
        return 'document';
    }
}
