<?php

namespace App\Modules\Finance\Filament\Resources\BillResource\Pages;

use App\Modules\Finance\Filament\Resources\BillResource;
use App\Platform\Recurrence\RecurrenceService;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateBill extends CreateRecord
{
    protected static string $resource = BillResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['household_id'] = Filament::getTenant()?->getKey();
        $data['created_by'] = auth()->id();
        $data['recurrence_rule'] = RecurrenceService::ruleFromChoice($this->data['recurrence'] ?? 'none');

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        return static::getModel()::create($data);
    }
}
