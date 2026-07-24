<?php

namespace App\Platform\Dashboard;

use App\Platform\Contracts\DashboardWidgetContract;
use App\Platform\Models\Household;
use Illuminate\Support\Collection;

/**
 * Agregira dashboard widgete registrovane u config/homeos-apps.php (ključ
 * `dashboard_widget`, CLAUDE.md §7/§12). Dashboard NE zna pojedinačno za module
 * niti dira njihove tabele — samo iterira registrovane widgete. Graceful sa 0
 * modula.
 */
class DashboardWidgetRegistry
{
    /**
     * @return Collection<int, DashboardWidgetContract>
     */
    public function providers(): Collection
    {
        return collect(config('homeos-apps', []))
            ->filter(fn (array $app) => ($app['enabled'] ?? true) && ! empty($app['dashboard_widget']))
            ->map(fn (array $app) => app($app['dashboard_widget']))
            ->filter(fn ($widget) => $widget instanceof DashboardWidgetContract)
            ->values();
    }

    /**
     * Filament widget klase za renderovanje — samo one koje imaju sadržaj za dato
     * domaćinstvo.
     *
     * @return array<int, string>
     */
    public function widgetClassesFor(Household $household): array
    {
        return $this->providers()
            ->filter(fn (DashboardWidgetContract $w) => $w->hasContentFor($household))
            ->map(fn (DashboardWidgetContract $w) => $w->widgetClass())
            ->values()
            ->all();
    }

    /**
     * Naslovi sekcija koje danas imaju sadržaj — za sažetak u hero traci.
     *
     * @return array<int, string>
     */
    public function activeTitlesFor(Household $household): array
    {
        return $this->providers()
            ->filter(fn (DashboardWidgetContract $w) => $w->hasContentFor($household))
            ->map(fn (DashboardWidgetContract $w) => $w->title())
            ->values()
            ->all();
    }
}
