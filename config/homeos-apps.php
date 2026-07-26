<?php

use App\Modules\Finance\Calendar\BillCalendarSource;
use App\Modules\Finance\Dashboard\FinanceDashboardWidget;
use App\Modules\Finance\Digest\BillDigestSource;
use App\Modules\Finance\QuickCapture\BillQuickCreate;
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
use App\Modules\Pets\Calendar\CareCalendarSource;
use App\Modules\Pets\Dashboard\PetsDashboardWidget;
use App\Modules\Pets\Digest\CareDigestSource;
use App\Modules\Pets\Enums\PetSpecies;
use App\Modules\Pets\QuickCapture\PetQuickCreate;
use App\Modules\Pets\Search\PetSearchProvider;
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
 *
 * VAŽNO (Faza 9c): svaki korisnički vidljiv tekst ovdje je **prijevodni ključ**
 * (`tasks.plural_label`), ne gotov tekst. Config se u produkciji kešira
 * (`config:cache`), pa `__()` pozvan ovdje zamrzne jezik onoga ko je pravio keš
 * — nazivi aplikacija bi ostali na jednom jeziku bez obzira na izbor korisnika.
 * Ključeve razrješavaju mjesta koja ih prikazuju (ModuleRegistry::name(),
 * QuickCaptureRegistry), a test provjerava da svaki ključ postoji u svim
 * jezicima.
 */

return [

    'tasks' => [
        'name' => 'tasks.plural_label',
        'icon' => 'heroicon-o-check-circle',
        'enabled' => true,
        'dashboard_widget' => TaskDashboardWidget::class,
        'search_provider' => TaskSearchProvider::class,
        'calendar_source' => TaskCalendarSource::class,
        'digest_source' => TaskDigestSource::class,
        'notification_categories' => ['task_assigned', 'task_due_soon'],
        'quick_capture' => [
            'label' => 'tasks.quick_capture',
            'icon' => 'heroicon-o-check-circle',
            'handler' => TaskQuickCreate::class,
            'fields' => [
                ['name' => 'title', 'label' => 'tasks.fields.title', 'type' => 'text', 'required' => true],
            ],
        ],
    ],

    'reminders' => [
        'name' => 'reminders.plural_label',
        'icon' => 'heroicon-o-bell',
        'enabled' => true,
        'dashboard_widget' => ReminderDashboardWidget::class,
        'search_provider' => ReminderSearchProvider::class,
        'calendar_source' => ReminderCalendarSource::class,
        'digest_source' => ReminderDigestSource::class,
        'notification_categories' => ['reminder_fired'],
        'quick_capture' => [
            'label' => 'reminders.quick_capture',
            'icon' => 'heroicon-o-bell',
            'handler' => ReminderQuickCreate::class,
            'fields' => [
                ['name' => 'title', 'label' => 'reminders.fields.title', 'type' => 'text', 'required' => true],
                ['name' => 'due_date', 'label' => 'reminders.fields.due_date', 'type' => 'datetime', 'required' => true],
            ],
        ],
    ],

    // Kalendar je "potrošač", ne izvor: nema svoj entitet niti providere, nego
    // prikazuje ono što drugi moduli prijave kroz calendar_source. U registryju je
    // zato što je za korisnika ravnopravna app u meniju — i može se isključiti.
    'calendar' => [
        'name' => 'calendar.title',
        'icon' => 'heroicon-o-calendar-days',
        'enabled' => true,
    ],

    'notes' => [
        'name' => 'notes.plural_label',
        'icon' => 'heroicon-o-document-text',
        'enabled' => true,
        'dashboard_widget' => NoteDashboardWidget::class,
        'search_provider' => NoteSearchProvider::class,
        'calendar_source' => JournalCalendarSource::class,
        'quick_capture' => [
            'label' => 'notes.quick_capture',
            'icon' => 'heroicon-o-document-text',
            'handler' => NoteQuickCreate::class,
            'fields' => [
                ['name' => 'body', 'label' => 'notes.fields.body', 'type' => 'textarea', 'required' => true],
            ],
        ],
    ],

    'finance' => [
        'name' => 'finance.navigation_group',
        'icon' => 'heroicon-o-banknotes',
        'enabled' => true,
        'dashboard_widget' => FinanceDashboardWidget::class,
        'search_provider' => FinanceSearchProvider::class,
        'calendar_source' => BillCalendarSource::class,
        'digest_source' => BillDigestSource::class,
        'notification_categories' => ['bill_due'],
        // Modul prikazuje iznose → domaćinstvo dobija izbor valute u postavkama.
        'uses_currency' => true,
        // Finansije nude dva tipa brzog unosa — lista definicija, javni ključevi
        // su `finance.expense` i `finance.bill` (vidi QuickCaptureRegistry).
        'quick_capture' => [
            [
                'key' => 'expense',
                'label' => 'finance.transactions.quick_capture',
                'icon' => 'heroicon-o-banknotes',
                'handler' => FinanceQuickCreate::class,
                'fields' => [
                    ['name' => 'title', 'label' => 'finance.transactions.fields.title', 'type' => 'text', 'required' => true],
                    ['name' => 'amount', 'label' => 'finance.transactions.fields.amount', 'type' => 'number', 'required' => true],
                ],
            ],
            [
                'key' => 'bill',
                'label' => 'finance.bills.quick_capture',
                'icon' => 'heroicon-o-document-currency-euro',
                'handler' => BillQuickCreate::class,
                'fields' => [
                    ['name' => 'title', 'label' => 'finance.bills.fields.title', 'type' => 'text', 'required' => true],
                    ['name' => 'amount', 'label' => 'finance.bills.fields.amount', 'type' => 'number', 'required' => true],
                    ['name' => 'due_date', 'label' => 'finance.bills.fields.due_date', 'type' => 'date', 'required' => true],
                ],
            ],
        ],
    ],

    // Kućni ljubimci — dodano u Fazi 7b kao DOKAZ PROŠIRIVOSTI: cijeli modul se
    // uklopio samo ovom registracijom, bez ijedne izmjene u core-u ili drugim
    // modulima (vidi ROADMAP Faza 7b i SUBMISSION.md).
    'pets' => [
        'name' => 'pets.navigation_label',
        'icon' => 'heroicon-o-heart',
        'enabled' => true,
        'dashboard_widget' => PetsDashboardWidget::class,
        'search_provider' => PetSearchProvider::class,
        'calendar_source' => CareCalendarSource::class,
        'digest_source' => CareDigestSource::class,
        'quick_capture' => [
            'label' => 'pets.quick_capture',
            'icon' => 'heroicon-o-heart',
            'handler' => PetQuickCreate::class,
            'fields' => [
                ['name' => 'name', 'label' => 'pets.fields.name', 'type' => 'text', 'required' => true],
                // Opcije kao callable — razrješavaju se u zahtjevu, jer prijevodi
                // nisu dostupni dok se config učitava (QuickCaptureRegistry).
                ['name' => 'species', 'label' => 'pets.fields.species', 'type' => 'select', 'required' => true,
                    'options' => [PetSpecies::class, 'options']],
            ],
        ],
    ],

    'lifeadmin' => [
        'name' => 'lifeadmin.navigation_group',
        'icon' => 'heroicon-o-folder',
        'enabled' => true,
        'dashboard_widget' => LifeAdminDashboardWidget::class,
        'search_provider' => LifeAdminSearchProvider::class,
        'calendar_source' => DocumentExpiryCalendarSource::class,
        'digest_source' => DocumentDigestSource::class,
    ],

];
