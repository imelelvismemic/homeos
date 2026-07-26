<?php

return [

    'title' => 'Kalendar',
    'navigation_group' => 'Organizacija',
    'day_click_hint' => 'Kliknite na dan da dodate zadatak, podsjetnik ili bilješku za taj datum.',

    // Tekst dugmadi i praznih stanja: naše, ne iz FullCalendar bundlea — bundle za
    // bs npr. nudi „Raspored" umjesto „Lista" (RULES.md §3). Nazive mjeseci i dana
    // formatira sam FullCalendar za aktivni jezik.
    'buttons' => [
        'today' => 'Danas',
        'month' => 'Mjesec',
        'week' => 'Sedmica',
        'day' => 'Dan',
        'list' => 'Lista',
    ],

    'all_day' => 'Cijeli dan',
    'no_events' => 'Nema događaja za prikaz',

    'empty' => [
        'heading' => 'Kalendar je prazan',
        'description' => 'Zadaci s rokom i drugi vremenski događaji pojaviće se ovdje automatski čim ih dodate.',
    ],

];
