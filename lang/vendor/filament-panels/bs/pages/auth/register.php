<?php

return [

    'title' => 'Registracija',

    'heading' => 'Kreirajte nalog',

    'actions' => [

        'login' => [
            'before' => 'ili',
            'label' => 'prijavite se na svoj nalog',
        ],

    ],

    'form' => [

        'email' => [
            'label' => 'E-mail adresa',
        ],

        'name' => [
            'label' => 'Ime i prezime',
        ],

        'password' => [
            'label' => 'Šifra',
            'validation_attribute' => 'šifra',
        ],

        'password_confirmation' => [
            'label' => 'Potvrda šifre',
        ],

        'actions' => [

            'register' => [
                'label' => 'Registrujte se',
            ],

        ],

    ],

    'notifications' => [

        'throttled' => [
            'title' => 'Previše pokušaja registracije',
            'body' => 'Pokušajte ponovo za :seconds sekundi.',
        ],

    ],

];
