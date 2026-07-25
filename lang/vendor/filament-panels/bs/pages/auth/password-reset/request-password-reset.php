<?php

return [

    'title' => 'Resetujte lozinku',

    'heading' => 'Zaboravili ste lozinku?',

    'actions' => [

        'login' => [
            'label' => 'nazad na prijavu',
        ],

    ],

    'form' => [

        'email' => [
            'label' => 'E-mail adresa',
        ],

        'actions' => [

            'request' => [
                'label' => 'Pošalji email',
            ],

        ],

    ],

    'notifications' => [

        'sent' => [
            'body' => 'Ako nalog s ovom email adresom postoji, dobit ćete email.',
        ],

        'throttled' => [
            'title' => 'Previše zahtjeva',
            'body' => 'Pokušajte ponovo za :seconds sekundi.',
        ],

    ],

];
