<?php

namespace App\Platform\Contracts;

use App\Models\User;
use App\Platform\Digest\DigestSection;
use App\Platform\Models\Household;
use Carbon\CarbonInterface;

/**
 * Modul koji ima nadolazeće stavke za digest email (Faza 6) implementira ovo i
 * registruje se u config/homeos-apps.php pod `digest_source`. Core (DigestService)
 * agregira sve registrovane izvore za dati period i člana — ne zna pojedinačno za
 * module (isti obrazac kao DashboardWidget/CalendarSource).
 */
interface DigestSourceContract
{
    /**
     * Vrati sekciju za digest datog člana u periodu [from, to], ili null ako nema
     * ništa. `$user` služi za filtriranje vidljivosti (Shareable) — u digest ulazi
     * samo ono što taj član smije vidjeti.
     */
    public function digestSection(Household $household, User $user, CarbonInterface $from, CarbonInterface $to): ?DigestSection;
}
