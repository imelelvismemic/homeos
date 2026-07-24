<?php

namespace Tests\Fixtures;

use App\Platform\Contracts\DashboardWidgetContract;
use App\Platform\Models\Household;

/** Simulira modul widget koji NEMA sadržaj za danas (prazno stanje). */
class EmptyDashboardWidget implements DashboardWidgetContract
{
    public function title(): string
    {
        return 'Računi';
    }

    public function widgetClass(): string
    {
        return 'App\\Fake\\BillsWidget';
    }

    public function hasContentFor(Household $household): bool
    {
        return false;
    }
}
