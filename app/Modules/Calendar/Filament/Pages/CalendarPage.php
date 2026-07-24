<?php

namespace App\Modules\Calendar\Filament\Pages;

use App\Platform\Calendar\CalendarService;
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
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static string $view = 'filament.calendar.pages.calendar';

    protected static ?int $navigationSort = 3;

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
     * Događaji za širok prozor (prošli mjesec → naredna dva) — dovoljno za
     * mjesečni/sedmični pregled bez dinamičkog dohvata po rasponu.
     *
     * @return array<int, array<string, mixed>>
     */
    public function events(): array
    {
        $household = Filament::getTenant();

        if (! $household instanceof Household) {
            return [];
        }

        return app(CalendarService::class)->fullCalendarEvents(
            now()->startOfMonth()->subMonth(),
            now()->endOfMonth()->addMonths(2),
            $household,
        );
    }
}
