<?php

namespace App\Platform\Filament\Resources;

use App\Platform\Filament\Concerns\BelongsToModule;
use Filament\Resources\Resource;

/**
 * Osnova za SVAKI Filament Resource unutar `app/Modules/*`.
 *
 * Nosi dvije stvari koje inače svaki modul mora zapamtiti sam — a zaboravljanje
 * se primijeti tek u produkciji:
 *
 * 1. **Rečenična kapitalizacija.** Filament po defaultu title-case-uje naslove
 *    izvedene iz labela ("Kućni Ljubimci", "Liste Za Kupovinu"). Bosanski piše
 *    veliko slovo samo na prvoj riječi (docs/RULES.md §2), pa gasimo
 *    `$hasTitleCaseModelLabel`. Ranije se to krpilo `getTitle()` override-om po
 *    stranici, pa se greška vraćala sa svakim novim modulom.
 * 2. **Pripadnost modulu** (`BelongsToModule`): isključen modul nestaje iz
 *    navigacije i vraća 403 na svojoj ruti (ROADMAP Faza 7a).
 *
 * Custom Page klase modula (Kanban, Kalendar, Mjesečni pregled) nisu Resource —
 * one koriste `BelongsToModule` direktno i naslov postavljaju kroz `getTitle()`.
 */
abstract class ModuleResource extends Resource
{
    use BelongsToModule;

    protected static bool $hasTitleCaseModelLabel = false;
}
