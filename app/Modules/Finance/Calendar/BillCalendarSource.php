<?php

namespace App\Modules\Finance\Calendar;

use App\Modules\Finance\Filament\Resources\BillResource;
use App\Modules\Finance\Models\Bill;
use App\Platform\Calendar\CalendarEvent;
use App\Platform\Contracts\CalendarSourceContract;
use App\Platform\Models\Household;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Računi s dospijećem na kalendaru (DATA_MODEL.md §10) — isti obrazac kao Task/
 * Reminder. Kalendar ne zna za Finansije.
 */
class BillCalendarSource implements CalendarSourceContract
{
    public function eventsBetween(CarbonInterface $start, CarbonInterface $end, Household $household): Collection
    {
        return Bill::query()
            ->where('household_id', $household->id)
            ->visibleTo(auth()->user())
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$start, $end])
            ->get()
            ->map(fn (Bill $bill) => new CalendarEvent(
                type: 'bill',
                id: $bill->id,
                title: $bill->title.' ('.number_format((float) $bill->amount, 2, ',', '.').' KM)',
                start: $bill->due_date->toIso8601String(),
                url: BillResource::getUrl('edit', ['record' => $bill, 'tenant' => $household]),
                color: $bill->isPaid() ? '#5E8C6A' : ($bill->due_date->isPast() ? '#B23B2E' : '#3E7C8C'),
                allDay: true,
            ));
    }

    public function type(): string
    {
        return 'bill';
    }
}
