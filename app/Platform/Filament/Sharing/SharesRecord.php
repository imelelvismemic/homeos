<?php

namespace App\Platform\Filament\Sharing;

use Illuminate\Database\Eloquent\Model;

/**
 * Za CreateRecord stranice: nakon kreiranja primijeni izbor vidljivosti iz forme
 * (polja iz SharingForm::components(), transientna). Objekt se kroz Shareable već
 * kreira kao household-vidljiv; ovdje ga po potrebi suzimo/proširimo. Radi samo za
 * Shareable modele — inače je no-op (npr. Kategorija/Budžet nisu Shareable).
 */
trait SharesRecord
{
    protected function applySharing(Model $record): void
    {
        if (! method_exists($record, 'share')) {
            return;
        }

        if (! isset($this->data['visibility'])) {
            return;
        }

        SharingForm::apply($record, [
            'visibility' => $this->data['visibility'],
            'share_members' => $this->data['share_members'] ?? [],
        ]);
    }
}
