<?php

namespace App\Platform\Filament\Concerns;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;

/**
 * Zajednički obrazac za Filament CreateRecord stranice modula: postavlja
 * household_id iz trenutnog tenanta i created_by iz korisnika, i kreira zapis
 * direktno (ownership preko relacije na zapisu — Household ne zna za module, §4).
 * DRY zamjena za ranije inline ponavljanje po modulima.
 */
trait CreatesForCurrentHousehold
{
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['household_id'] = Filament::getTenant()?->getKey();
        $data['created_by'] = auth()->id();

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        return static::getModel()::create($data);
    }
}
