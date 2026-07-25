<?php

// Paketski bs prijevod nema ovaj fajl (profil je bio na engleskom).
// docs/PRAVILA.md §1: paketske prijevode ispravljamo override-om, ne po stranicama.
return [

    'label' => 'Profil',

    'form' => [

        'email' => [
            'label' => 'E-mail adresa',
        ],

        'name' => [
            'label' => 'Ime i prezime',
        ],

        'password' => [
            'label' => 'Nova lozinka',
        ],

        'password_confirmation' => [
            'label' => 'Potvrdi novu lozinku',
        ],

        'actions' => [

            'save' => [
                'label' => 'Sačuvaj',
            ],

        ],

    ],

    'notifications' => [

        'saved' => [
            'title' => 'Sačuvano',
        ],

    ],

    'actions' => [

        'cancel' => [
            'label' => 'Zatvori',
        ],

    ],

];
