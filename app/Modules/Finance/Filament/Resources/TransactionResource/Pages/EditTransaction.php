<?php

namespace App\Modules\Finance\Filament\Resources\TransactionResource\Pages;

use App\Modules\Finance\Filament\Resources\TransactionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTransaction extends EditRecord
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['participants'] = $this->record->participants()->pluck('household_members.id')->all();

        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->participants()->sync($this->data['participants'] ?? []);
    }
}
