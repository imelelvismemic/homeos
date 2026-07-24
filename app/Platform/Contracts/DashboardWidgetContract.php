<?php

namespace App\Platform\Contracts;

use App\Platform\Models\Household;

/**
 * Svaki modul koji ima nešto za prikazati na "Today" dashboardu (Faza 2)
 * implementira ovaj interfejs i registruje ga u config/homeos-apps.php pod
 * ključem `dashboard_widget`. Core dashboard NE zna ništa o pojedinačnim
 * modulima — samo iterira registrovane widgete (CLAUDE.md tačka 7).
 */
interface DashboardWidgetContract
{
    /** Naziv koji se prikazuje kao naslov sekcije widgeta. */
    public function title(): string;

    /** Filament Widget klasa koja se renderuje na dashboardu. */
    public function widgetClass(): string;

    /** Da li widget ima šta prikazati za dato domaćinstvo (za prazna stanja). */
    public function hasContentFor(Household $household): bool;
}
