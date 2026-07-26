<?php

namespace App\Platform\Filament\Concerns;

use Filament\Actions\Action;

/**
 * "Nazad" na formi uređivanja vodi na LISTU tog modula, uvijek.
 *
 * Filament po defaultu radi `document.referrer ? window.history.back() : …`, pa
 * korisnik koji je upravo dodao zapis (dodavanje → snimi → forma uređivanja)
 * klikom na "Nazad" završi natrag na formi dodavanja — kao da ništa nije snimio.
 * Ovdje uklanjamo history.back() i vodimo ga na listu (docs/RULES.md §9).
 *
 * Koristi ga SVAKA EditRecord stranica u sistemu; novi modul ga dodaje po
 * checklistu iz CLAUDE.md §14.
 */
trait CancelReturnsToList
{
    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->alpineClickHandler(null)
            ->url(static::getResource()::getUrl('index'));
    }
}
