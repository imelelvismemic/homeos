<?php

namespace App\Modules\Reminders\Filament\Resources\ReminderResource\Pages;

use App\Modules\Reminders\Filament\Resources\ReminderResource;
use App\Platform\Recurrence\RecurrenceService;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateReminder extends CreateRecord
{
    protected static string $resource = ReminderResource::class;

    public function getTitle(): string
    {
        return __('reminders.headings.create');
    }

    public function getBreadcrumb(): string
    {
        return __('reminders.headings.create');
    }

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
