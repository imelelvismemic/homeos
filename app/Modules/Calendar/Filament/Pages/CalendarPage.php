<?php

namespace App\Modules\Calendar\Filament\Pages;

use App\Platform\Filament\Concerns\BelongsToModule;
use App\Platform\Models\Household;
use Filament\Facades\Filament;
use Filament\Pages\Page;

/**
 * Kalendar (ROADMAP Faza 3). Ne uvodi vlastiti "događaj" entitet — agregira sve
 * registrovane CalendarSourceContract izvore preko platforme (CalendarService).
 * Zadatak s rokom se ovdje pojavljuje automatski jer Tasks modul registruje svoj
 * izvor u config/homeos-apps.php; kalendar ne zna pojedinačno za module.
 *
 * FullCalendar je self-hosted (resources/js/calendar.js, bundlan Viteom) jer
 * community Filament plugin ne podržava Laravel 13.
 */
class CalendarPage extends Page
{
    use BelongsToModule;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static string $view = 'filament.calendar.pages.calendar';

    protected static ?int $navigationSort = 5;

    public static function getNavigationLabel(): string
    {
        return __('calendar.title');
    }

    public function getTitle(): string
    {
        return __('calendar.title');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('calendar.navigation_group');
    }

    /**
     * URL feeda događaja. FullCalendar sam javlja raspon koji prikazuje, pa se
     * događaji dohvataju po potrebi i mogu se ponovo učitati (nakon brzog
     * dodavanja) bez promjene prikazanog mjeseca.
     */
    public function eventsUrl(): ?string
    {
        $household = Filament::getTenant();

        if (! $household instanceof Household) {
            return null;
        }

        return route('filament.app.calendar-events', ['h' => $household->getKey()]);
    }

    /**
     * Podešavanja koja Blade predaje FullCalendaru.
     *
     * Jezik ide odavde jer FullCalendar sam formatira nazive mjeseci i dana za
     * dati jezik — ranije su bili fiksni bosanski nizovi u JS-u, pa je kalendar
     * bio jedini ekran koji nije pratio izbor jezika.
     *
     * Tekst dugmadi NE dolazi iz FullCalendar bundlea nego iz prijevoda: bundle
     * za bosanski nudi „Raspored" umjesto našeg „Lista" (RULES.md §3 — isti
     * termin za istu radnju svuda).
     *
     * @return array<string, mixed>
     */
    public function calendarOptions(): array
    {
        return [
            'locale' => app()->getLocale(),
            'labels' => [
                'today' => __('calendar.buttons.today'),
                'month' => __('calendar.buttons.month'),
                'week' => __('calendar.buttons.week'),
                'day' => __('calendar.buttons.day'),
                'list' => __('calendar.buttons.list'),
                'allDay' => __('calendar.all_day'),
                'noEvents' => __('calendar.no_events'),
            ],
        ];
    }
}
