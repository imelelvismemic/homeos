<?php

namespace App\Modules\Finance\Filament\Resources\TransactionResource\Pages;

use App\Modules\Finance\Filament\Resources\TransactionResource;
use App\Platform\Filament\Concerns\CreatesForCurrentHousehold;
use Filament\Resources\Pages\CreateRecord;

class CreateTransaction extends CreateRecord
{
    use CreatesForCurrentHousehold;

    protected static string $resource = TransactionResource::class;

    protected function afterCreate(): void
    {
        $this->record->participants()->sync($this->data['participants'] ?? []);
    }
}
