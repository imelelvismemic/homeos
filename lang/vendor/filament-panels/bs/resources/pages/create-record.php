<?php

// Override paketskih bs prijevoda (Laravel array_replace_recursive spaja preko
// paketskog fajla). Ujednačena terminologija dugmadi — vidi docs/PRAVILA_PREVODA.md.

return [

    'form' => [

        'actions' => [

            'cancel' => [
                'label' => 'Zatvori',
            ],

            'create' => [
                'label' => 'Sačuvaj',
            ],

            'create_another' => [
                'label' => 'Sačuvaj i dodaj novi',
            ],

        ],

    ],

];
