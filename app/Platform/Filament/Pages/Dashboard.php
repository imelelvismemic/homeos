<?php

namespace App\Platform\Filament\Pages;

use App\Platform\Dashboard\DashboardWidgetRegistry;
use Filament\Facades\Filament;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * "Today" dashboard (ROADMAP Faza 2). Signature element je dnevni-brief hero
 * (pozdrav + datum + sažetak) iznad widget mreže. Widgeti dolaze iz registryja
 * (config/homeos-apps.php), ne direktnim upitima u module — pa se renderuje bez
 * grešaka i sa 0 instaliranih modula (DoD Faze 2).
 */
class Dashboard extends BaseDashboard
{
    protected static string $view = 'filament.platform.pages.dashboard';

    public function getTitle(): string
    {
        return __('platform.dashboard.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('platform.dashboard.title');
    }

    public function getWidgets(): array
    {
        $household = Filament::getTenant();

        return $household
            ? app(DashboardWidgetRegistry::class)->widgetClassesFor($household)
            : [];
    }

    /** Pozdrav ovisno o dobu dana. */
    public function heroGreeting(): string
    {
        $hour = (int) Carbon::now()->format('G');

        $part = match (true) {
            $hour < 11 => 'morning',
            $hour < 18 => 'day',
            default => 'evening',
        };

        return __("platform.dashboard.greeting.{$part}", [
            'name' => Filament::auth()->user()->name,
        ]);
    }

    /** Današnji datum na bosanskom (npr. "Četvrtak, 24. juli 2026."). */
    public function heroDate(): string
    {
        return Str::ucfirst(Carbon::now()->translatedFormat('l, j. F Y.'));
    }

    /**
     * Sažetak dana: naslovi sekcija koje imaju sadržaj. Prazno kad nema modula
     * ili nema ničega za danas.
     *
     * @return array<int, string>
     */
    public function heroSummary(): array
    {
        $household = Filament::getTenant();

        return $household
            ? app(DashboardWidgetRegistry::class)->activeTitlesFor($household)
            : [];
    }
}
