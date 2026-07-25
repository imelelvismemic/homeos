<?php

namespace App\Modules\Finance\Filament\Resources\BillResource\Pages;

use App\Modules\Finance\Filament\Resources\BillResource;
use App\Platform\Recurrence\RecurrenceService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBill extends EditRecord
{
    protected static string $resource = BillResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['recurrence'] = RecurrenceService::choiceFromRule($this->record->recurrence_rule);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['recurrence_rule'] = RecurrenceService::ruleFromChoice($this->data['recurrence'] ?? 'none');

        return $data;
    }
}
