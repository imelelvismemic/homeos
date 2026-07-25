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
            'label' => 'Lozinka',
            'validation_attribute' => 'lozinka',
        ],

        'password_confirmation' => [
            'label' => 'Potvrda lozinke',
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
