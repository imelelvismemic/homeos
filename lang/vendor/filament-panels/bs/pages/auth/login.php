<?php

// Dopuna nedostajućih ključeva iz vendor/filament/filament/resources/lang/bs/pages/auth/login.php
// (Laravel spaja ovo preko postojećeg prijevoda — vidi FileLoader::loadNamespaceOverrides).
return [

    'actions' => [

        'register' => [
            'before' => 'ili',
            'label' => 'napravite nalog',
        ],

        'request_password_reset' => [
            'label' => 'Zaboravili ste šifru?',
        ],

    ],

];
