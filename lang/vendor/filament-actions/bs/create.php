<?php

// Override paketskih bs prijevoda za "create" akciju (koristi je i relation
// manager, npr. dodavanje podzadatka). Vidi docs/RULES.md.

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
