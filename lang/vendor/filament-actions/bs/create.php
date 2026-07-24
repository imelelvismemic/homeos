<?php

// Override paketskih bs prijevoda za "create" akciju (koristi je i relation
// manager, npr. dodavanje podzadatka). Vidi docs/PRAVILA.md.

return [

    'single' => [

        'modal' => [

            'actions' => [

                'create' => [
                    'label' => 'Sačuvaj',
                ],

                'create_another' => [
                    'label' => 'Sačuvaj i dodaj novi',
                ],

            ],

        ],

    ],

];
