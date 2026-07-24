<?php

namespace App\Modules\Reminders\Filament\Resources\ReminderResource\Pages;

use App\Modules\Reminders\Filament\Resources\ReminderResource;
use App\Platform\Recurrence\RecurrenceService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditReminder extends EditRecord
{
    protected static string $resource = ReminderResource::class;

    public function getTitle(): string
    {
        return __('reminders.headings.edit');
    }

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
