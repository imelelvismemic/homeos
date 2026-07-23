<?php

return [

    'title' => 'Resetujte šifru',

    'heading' => 'Resetujte šifru',

    'form' => [

        'email' => [
            'label' => 'E-mail adresa',
        ],

        'password' => [
            'label' => 'Šifra',
            'validation_attribute' => 'šifra',
        ],

        'password_confirmation' => [
            'label' => 'Potvrda šifre',
        ],

        'actions' => [

            'reset' => [
                'label' => 'Resetuj šifru',
            ],

        ],

    ],

    'notifications' => [

        'throttled' => [
            'title' => 'Previše pokušaja resetovanja',
            'body' => 'Pokušajte ponovo za :seconds sekundi.',
        ],

    ],

];
