<?php

return [

    'title' => 'Resetujte lozinku',

    'heading' => 'Resetujte lozinku',

    'form' => [

        'email' => [
            'label' => 'E-mail adresa',
        ],

        'password' => [
            'label' => 'Nova lozinka',
            'validation_attribute' => 'lozinka',
        ],

        'password_confirmation' => [
            'label' => 'Potvrda lozinke',
        ],

        'actions' => [

            'reset' => [
                'label' => 'Resetuj lozinku',
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
