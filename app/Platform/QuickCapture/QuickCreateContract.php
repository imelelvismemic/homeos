<?php

namespace App\Platform\QuickCapture;

use App\Models\User;
use App\Platform\Models\Household;

/**
 * Brzo dodavanje (ROADMAP Faza 2.4). Modul registruje handler u
 * config/homeos-apps.php pod `quick_capture.handler`; generički
 * QuickCreateController ga koristi da iz modala (Alpine + fetch) kreira zapis,
 * bez navigacije sa trenutne stranice. Core ne zna za pojedinačne module.
 */
interface QuickCreateContract
{
    /**
     * Validaciona pravila za polja brzog dodavanja (Laravel validator).
     *
     * @return array<string, mixed>
     */
    public function rules(): array;

    /**
     * Kreira zapis iz validiranih podataka, u datom domaćinstvu i za korisnika.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, Household $household, User $user): void;
}
