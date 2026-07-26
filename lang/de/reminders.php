<?php

return [

    'label' => 'Erinnerung',
    'plural_label' => 'Erinnerungen',
    'navigation_label' => 'Erinnerungen',
    'navigation_group' => 'Organisation',

    'fields' => [
        'title' => 'Titel',
        'description' => 'Beschreibung',
        'due_date' => 'Zeitpunkt',
        'due_date_now' => 'Jetzt',
        'recurrence' => 'Wiederholung',
        'assigned_to' => 'Zuständige Person',
        'assigned_to_help' => 'Für wen die Erinnerung gedacht ist. Bleibt das Feld leer, geht sie an Sie.',
        'status' => 'Status',
    ],

    'recurrence' => [
        'none' => 'Keine Wiederholung',
        'daily' => 'Täglich',
        'weekly' => 'Wöchentlich',
        'monthly' => 'Monatlich',
        'yearly' => 'Jährlich',
    ],

    'status' => [
        'pending' => 'Aktiv',
        'done' => 'Ausgelöst',
    ],

    'filters' => [
        'hide_done' => 'Ausgelöste ausblenden',
    ],

    'actions' => [
        'create' => 'Erinnerung hinzufügen',
        'complete' => 'Als ausgelöst markieren',
        'completed_notice' => 'Die Erinnerung wurde ausgelöst — die Benachrichtigung ist verschickt.',
    ],

    'headings' => [
        'create' => 'Erinnerung hinzufügen',
        'edit' => 'Erinnerung bearbeiten',
        'delete' => 'Erinnerung löschen',
        'delete_description' => 'Die Erinnerung „:title“ wirklich löschen? Das kann nicht rückgängig gemacht werden.',
    ],

    'empty' => [
        'heading' => 'Noch keine Erinnerungen',
        'description' => 'Fügen Sie eine Erinnerung mit Zeitpunkt hinzu — wir melden uns, wenn es soweit ist. Sie erscheint auch im Kalender.',
    ],

    'widget' => [
        'heading' => 'Erinnerungen für heute',
        'none' => 'Keine Erinnerungen für heute. 🔔',
    ],

    'notifications' => [
        'due' => [
            'subject' => 'Erinnerung',
            'line' => 'Erinnerung: „:title“.',
            'action' => 'Erinnerung öffnen',
        ],
    ],

    'quick_capture' => 'Neue Erinnerung',

    'calendar_type' => 'Erinnerung',

];
