<?php

namespace Tests\Fixtures;

use App\Platform\Contracts\DashboardWidgetContract;
use App\Platform\Models\Household;

/** Simulira modul widget koji IMA sadržaj za danas. */
class FakeDashboardWidget implements DashboardWidgetContract
{
    public function title(): string
    {
        return 'Zadaci';
    }

    public function widgetClass(): string
    {
        return 'App\\Fake\\TasksWidget';
    }

    public function hasContentFor(Household $household): bool
    {
        return true;
    }
}
