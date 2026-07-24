<?php

namespace App\Platform\Contracts;

use App\Platform\Calendar\CalendarEvent;
use App\Platform\Models\Household;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Modul koji ima datume za prikaz u kalendaru (Zadaci: due_date; kasnije Računi,
 * Podsjetnici) implementira ovaj interfejs i registruje ga u config/homeos-apps.php
 * pod `calendar_source` (DATA_MODEL.md §10). Kalendar agregira sve izvore — ne
 * duplira podatke i ne importuje tuđe modele (CLAUDE.md §9/§18).
 */
interface CalendarSourceContract
{
    /**
     * Događaji u datom rasponu za dato domaćinstvo.
     *
     * @return Collection<int, CalendarEvent>
     */
    public function eventsBetween(CarbonInterface $start, CarbonInterface $end, Household $household): Collection;

    /** Ključ tipa izvora, npr. 'task'. */
    public function type(): string;
}
