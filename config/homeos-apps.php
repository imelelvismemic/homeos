<?php

use App\Modules\Tasks\Calendar\TaskCalendarSource;
use App\Modules\Tasks\Dashboard\TaskDashboardWidget;
use App\Modules\Tasks\Search\TaskSearchProvider;

/**
 * App Registry — vidi CLAUDE.md tačku 12 i DATA_MODEL.md tačku 6.
 *
 * Core (dashboard, search, navigacija) čita isključivo odavde — nikad
 * hardkodovana lista modula u Blade/Filament kodu. Svaki modul se registruje
 * ovdje sa svojim ekstenzijskim tačkama (dashboard_widget, search_provider,
 * calendar_source, quick_capture). Core ne zna pojedinačno za module.
 */

return [

    'tasks' => [
        'name' => 'Zadaci',
        'icon' => 'heroicon-o-check-circle',
        'enabled' => true,
        'dashboard_widget' => TaskDashboardWidget::class,
        'search_provider' => TaskSearchProvider::class,
        'calendar_source' => TaskCalendarSource::class,
        'quick_capture' => [
            'label' => 'Novi zadatak',
            'icon' => 'heroicon-o-check-circle',
            'url' => 'filament.app.resources.tasks.create',
        ],
    ],

];
