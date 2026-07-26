<?php

// Dopuna nedostajućih ključeva iz vendor/filament/tables/resources/lang/bs/table.php
// (Laravel spaja vendor override rekurzivno preko postojećeg bs prijevoda).
// bs verzija paketa nema `fields.search`, pa se prikazuje engleski "Search".
return [

    'fields' => [

        'search' => [
            'label' => 'Pretraga',
            'placeholder' => 'Pretraga',
            'indicator' => 'Pretraga',
        ],

    ],

    // Naslov padajućeg menija za prikaz/skrivanje kolona ("Columns"). Paketski bs
    // fajl nema ovaj ključ, pa je Laravel padao na engleski fallback na SVIM
    // listama (ne samo zadacima) — vidi docs/RULES.md §1.
    'column_toggle' => [
        'heading' => 'Kolone',
    ],

    'actions' => [

        'toggle_columns' => [
            'label' => 'Kolone',
        ],

    ],

    'empty' => [
        'description' => 'Dodajte prvu stavku da započnete.',
    ],

];
