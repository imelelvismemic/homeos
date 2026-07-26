<?php

namespace App\Modules\Pets\Calendar;

use App\Modules\Pets\Filament\Resources\PetResource;
use App\Modules\Pets\Models\CareRecord;
use App\Platform\Calendar\CalendarEvent;
use App\Platform\Contracts\CalendarSourceContract;
use App\Platform\Models\Household;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Termini njege na kalendaru — isti obrazac kao Task/Reminder/Bill/Document.
 * Kalendar ne zna za Ljubimce; dovoljna je registracija u homeos-apps.php.
 */
class CareCalendarSource implements CalendarSourceContract
{
    public function eventsBetween(CarbonInterface $start, CarbonInterface $end, Household $household): Collection
    {
        return CareRecord::query()
            ->where('household_id', $household->id)
            ->visibleTo(auth()->user())
            ->whereBetween('due_date', [$start, $end])
            ->with('pet')
            ->get()
            ->map(fn (CareRecord $record) => new CalendarEvent(
                type: 'pet_care',
                id: $record->id,
                title: $record->displayTitle(),
                start: $record->due_date->toIso8601String(),
                url: PetResource::getUrl('edit', ['record' => $record->pet_id, 'tenant' => $household]),
                color: $record->isDone() ? '#5E8C6A' : '#A05FB4',
            ));
    }

    public function type(): string
    {
        return 'pet_care';
    }
}
