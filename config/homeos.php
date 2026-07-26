<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Verzija aplikacije
    |--------------------------------------------------------------------------
    | Jedno mjesto istine — koristi je health endpoint (i footer u Fazi 9), da se
    | broj ne prepisuje po layoutima.
    |
    | NAMJERNO nije `env()`: verzija je svojstvo KODA, ne instalacije. Dok je
    | stajala u `.env`, morala se ručno mijenjati na serveru pri svakom izdanju —
    | i već je bila odstupila (kod 1.0.1, server .env 1.0.0), pa bi footer mirno
    | prikazivao pogrešan broj. Ovako se podiže u commitu, kao i sve ostalo.
    */

    'version' => '1.0.0',

    /*
    |--------------------------------------------------------------------------
    | Adresa za tehnička upozorenja
    |--------------------------------------------------------------------------
    | Ovdje stižu poruke o infrastrukturi (npr. neuspio noćni backup) — to nije
    | korisničko obavještenje pa ne ide kroz postavke po članu. Ako nije
    | postavljena, koristi se email vlasnika prvog domaćinstva.
    */

    'alert_email' => env('HOMEOS_ALERT_EMAIL'),

    /*
    |--------------------------------------------------------------------------
    | Backup (ROADMAP Faza 8)
    |--------------------------------------------------------------------------
    | Dnevni dump baze + arhiva korisničkih priloga. Pokreće ga centralni
    | scheduler (`homeos:backup`), a ne cron na hostu — tako je cijela strategija
    | u repou, testirana i preživi seobu servera.
    |
    | `path` je unutar kontejnera; u produkciji je bind-mountan na host folder
    | (docker-compose.prod.yml), da backupi prežive redeploy.
    */

    'backup' => [
        'path' => env('BACKUP_PATH', storage_path('backups')),
        'keep_days' => (int) env('BACKUP_KEEP_DAYS', 14),
    ],

];
