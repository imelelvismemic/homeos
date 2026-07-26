<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Verzija aplikacije
    |--------------------------------------------------------------------------
    | Jedno mjesto istine — koristi je health endpoint (i footer u Fazi 9), da se
    | broj ne prepisuje po layoutima.
    */

    'version' => env('HOMEOS_VERSION', '1.0.0'),

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
