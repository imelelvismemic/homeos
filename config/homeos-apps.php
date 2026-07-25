<?php

use App\Modules\Finance\Calendar\BillCalendarSource;
use App\Modules\Finance\Dashboard\FinanceDashboardWidget;
use App\Modules\Finance\Digest\BillDigestSource;
use App\Modules\Finance\QuickCapture\FinanceQuickCreate;
use App\Modules\Finance\Search\FinanceSearchProvider;
use App\Modules\LifeAdmin\Calendar\DocumentExpiryCalendarSource;
use App\Modules\LifeAdmin\Dashboard\LifeAdminDashboardWidget;
use App\Modules\LifeAdmin\Digest\DocumentDigestSource;
use App\Modules\LifeAdmin\Search\LifeAdminSearchProvider;
use App\Modules\Notes\Calendar\JournalCalendarSource;
use App\Modules\Notes\Dashboard\NoteDashboardWidget;
use App\Modules\Notes\QuickCapture\NoteQuickCreate;
use App\Modules\Notes\Search\NoteSearchProvider;
use App\Modules\Reminders\Calendar\ReminderCalendarSource;
use App\Modules\Reminders\Dashboard\ReminderDashboardWidget;
use App\Modules\Reminders\Digest\ReminderDigestSource;
use App\Modules\Reminders\QuickCapture\ReminderQuickCreate;
use App\Modules\Reminders\Search\ReminderSearchProvider;
use App\Modules\Tasks\Calendar\TaskCalendarSource;
use App\Modules\Tasks\Dashboard\TaskDashboardWidget;
use App\Modules\Tasks\Digest\TaskDigestSource;
use App\Modules\Tasks\QuickCapture\TaskQuickCreate;
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
        'digest_source' => TaskDigestSource::class,
        'notification_categories' => ['task_assigned', 'task_due_soon'],
        'quick_capture' => [
            'label' => 'Novi zadatak',
            'icon' => 'heroicon-o-check-circle',
            'handler' => TaskQuickCreate::class,
            'fields' => [
                ['name' => 'title', 'label' => 'Naslov', 'type' => 'text', 'required' => true],
            ],
        ],
    ],

    'reminders' => [
        'name' => 'Podsjetnici',
        'icon' => 'heroicon-o-bell',
        'enabled' => true,
        'dashboard_widget' => ReminderDashboardWidget::class,
        'search_provider' => ReminderSearchProvider::class,
        'calendar_source' => ReminderCalendarSource::class,
        'digest_source' => ReminderDigestSource::class,
        'notification_categories' => ['reminder_fired'],
        'quick_capture' => [
            'label' => 'Novi podsjetnik',
            'icon' => 'heroicon-o-bell',
            'handler' => ReminderQuickCreate::class,
            'fields' => [
                ['name' => 'title', 'label' => 'Naslov', 'type' => 'text', 'required' => true],
                ['name' => 'due_date', 'label' => 'Vrijeme', 'type' => 'datetime', 'required' => true],
            ],
        ],
    ],

    'notes' => [
        'name' => 'Bilješke',
        'icon' => 'heroicon-o-document-text',
        'enabled' => true,
        'dashboard_widget' => NoteDashboardWidget::class,
        'search_provider' => NoteSearchProvider::class,
        'calendar_source' => JournalCalendarSource::class,
        'quick_capture' => [
            'label' => 'Nova bilješka',
            'icon' => 'heroicon-o-document-text',
            'handler' => NoteQuickCreate::class,
            'fields' => [
                ['name' => 'body', 'label' => 'Sadržaj', 'type' => 'textarea', 'required' => true],
            ],
        ],
    ],

    'finance' => [
        'name' => 'Finansije',
        'icon' => 'heroicon-o-banknotes',
        'enabled' => true,
        'dashboard_widget' => FinanceDashboardWidget::class,
        'search_provider' => FinanceSearchProvider::class,
        'calendar_source' => BillCalendarSource::class,
        'digest_source' => BillDigestSource::class,
        'notification_categories' => ['bill_due'],
        'quick_capture' => [
            'label' => 'Novi trošak',
            'icon' => 'heroicon-o-banknotes',
            'handler' => FinanceQuickCreate::class,
            'fields' => [
                ['name' => 'title', 'label' => 'Naziv', 'type' => 'text', 'required' => true],
                ['name' => 'amount', 'label' => 'Iznos (KM)', 'type' => 'number', 'required' => true],
            ],
        ],
    ],

    'lifeadmin' => [
        'name' => 'Administracija',
        'icon' => 'heroicon-o-folder',
        'enabled' => true,
        'dashboard_widget' => LifeAdminDashboardWidget::class,
        'search_provider' => LifeAdminSearchProvider::class,
        'calendar_source' => DocumentExpiryCalendarSource::class,
        'digest_source' => DocumentDigestSource::class,
    ],

];
