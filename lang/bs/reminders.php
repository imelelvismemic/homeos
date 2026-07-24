<?php

return [

    'label' => 'Podsjetnik',
    'plural_label' => 'Podsjetnici',
    'navigation_label' => 'Podsjetnici',
    'navigation_group' => 'Organizacija',

    'fields' => [
        'title' => 'Naslov',
        'description' => 'Opis',
        'due_date' => 'Vrijeme',
        'due_date_now' => 'Sada',
        'recurrence' => 'Ponavljanje',
        'status' => 'Status',
    ],

    'recurrence' => [
        'none' => 'Ne ponavlja se',
        'daily' => 'Dnevno',
        'weekly' => 'Sedmično',
        'monthly' => 'Mjesečno',
        'yearly' => 'Godišnje',
    ],

    'status' => [
        'pending' => 'Aktivan',
        'done' => 'Okinut',
    ],

    'filters' => [
        'hide_done' => 'Sakrij okinute',
    ],

    'actions' => [
        'create' => 'Dodaj podsjetnik',
        'complete' => 'Označi okinutim',
    ],

    'headings' => [
        'create' => 'Dodaj podsjetnik',
        'edit' => 'Uredi podsjetnik',
    ],

    'empty' => [
        'heading' => 'Još nema podsjetnika',
        'description' => 'Dodajte podsjetnik s vremenom — obavijestićemo vas kad dođe. Pojaviće se i na kalendaru.',
    ],

    'widget' => [
        'heading' => 'Podsjetnici za danas',
        'none' => 'Nema podsjetnika za danas. 🔔',
    ],

    'notifications' => [
        'due' => [
            'subject' => 'Podsjetnik',
            'line' => 'Podsjetnik: ":title".',
            'action' => 'Otvori podsjetnik',
        ],
    ],

    'quick_capture' => 'Novi podsjetnik',

    'calendar_type' => 'Podsjetnik',

];
