<?php

namespace App\Platform\Calendar;

use App\Platform\Contracts\CalendarSourceContract;
use App\Platform\Models\Household;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Agregira događaje svih registrovanih kalendar izvora (DATA_MODEL.md §10).
 * Izvore čita ISKLJUČIVO iz config/homeos-apps.php (`calendar_source`) — ne zna
 * pojedinačno za module. Graceful sa 0 modula.
 */
class CalendarService
{
    /**
     * @return Collection<int, CalendarEvent>
     */
    public function eventsBetween(CarbonInterface $start, CarbonInterface $end, Household $household): Collection
    {
        return collect(config('homeos-apps', []))
            ->filter(fn (array $app) => ($app['enabled'] ?? true) && ! empty($app['calendar_source']))
            ->map(fn (array $app) => app($app['calendar_source']))
            ->filter(fn ($source) => $source instanceof CalendarSourceContract)
            ->flatMap(fn (CalendarSourceContract $source) => $source->eventsBetween($start, $end, $household))
            ->values();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function fullCalendarEvents(CarbonInterface $start, CarbonInterface $end, Household $household): array
    {
        return $this->eventsBetween($start, $end, $household)
            ->map(fn (CalendarEvent $event) => $event->toFullCalendar())
            ->all();
    }
}
