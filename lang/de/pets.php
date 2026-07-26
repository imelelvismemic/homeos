<?php

return [

    'label' => 'Haustier',
    'plural_label' => 'Haustiere',
    'navigation_label' => 'Haustiere',
    'navigation_group' => 'Verwaltung',

    'fields' => [
        'name' => 'Name',
        'species' => 'Tierart',
        'birth_date' => 'Geburtsdatum',
        'birth_date_help' => 'Optional — wird für die Altersanzeige verwendet.',
        'notes' => 'Notiz',
        'care_count' => 'Pflegetermine',
    ],

    'species' => [
        'dog' => 'Hund',
        'cat' => 'Katze',
        'bird' => 'Vogel',
        'fish' => 'Fisch',
        'other' => 'Sonstiges',
    ],

    'headings' => [
        'create' => 'Haustier hinzufügen',
        'edit' => 'Haustier bearbeiten',
        'delete' => 'Haustier löschen',
        'delete_description' => 'Das Haustier „:name“ wirklich löschen? Alle Pflegetermine werden mitgelöscht. Das kann nicht rückgängig gemacht werden.',
    ],

    'empty' => [
        'heading' => 'Noch keine Haustiere',
        'description' => 'Fügen Sie ein Haustier hinzu und tragen Sie Impfungen und Untersuchungen ein — wir erinnern Sie rechtzeitig.',
    ],

    'care' => [
        'title' => 'Pflege und Gesundheit',
        'create' => 'Pflegetermin hinzufügen',
        'complete' => 'Als erledigt markieren',
        'delete' => 'Pflegetermin löschen',
        'delete_description' => '„:title“ wirklich löschen? Das kann nicht rückgängig gemacht werden.',
        'empty' => 'Noch keine Pflegetermine',
        'empty_description' => 'Tragen Sie Impfung, Untersuchung oder Behandlung mit Datum ein — Erinnerung und Kalender erledigen den Rest.',
        'display_title' => ':type · :pet',
        'reminder' => 'Bald: :type für :pet',

        'fields' => [
            'type' => 'Art der Pflege',
            'due_date' => 'Termin',
            'remind_days_before' => 'Tage vorher erinnern',
            'remind_days_before_help' => 'Wie viele Tage vor dem Termin Sie die Erinnerung möchten.',
            'notes' => 'Notiz',
            'status' => 'Status',
        ],

        'types' => [
            'vaccination' => 'Impfung',
            'vet_visit' => 'Tierarztbesuch',
            'treatment' => 'Behandlung',
            'grooming' => 'Fellpflege',
            'other' => 'Sonstiges',
        ],

        'status' => [
            'planned' => 'Geplant',
            'done' => 'Erledigt',
        ],
    ],

    'widget' => [
        'heading' => 'Haustierpflege',
        'none' => 'Keine Pflegetermine in den nächsten Tagen. 🐾',
    ],

    'digest' => [
        'line' => ':title — :date',
    ],

    'quick_capture' => 'Neues Haustier',

];
