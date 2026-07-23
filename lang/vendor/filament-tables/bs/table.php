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

];
