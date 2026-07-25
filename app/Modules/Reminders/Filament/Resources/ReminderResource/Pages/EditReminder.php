<?php

namespace App\Modules\Reminders\Filament\Resources\ReminderResource\Pages;

use App\Modules\Reminders\Filament\Resources\ReminderResource;
use App\Platform\Recurrence\RecurrenceService;
use Filament\Actions\Action;
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
        return [
            Action::make('complete')
                ->label(__('reminders.actions.complete'))
                ->icon('heroicon-m-check')
                ->color('success')
                ->visible(fn () => $this->record->completed_at === null)
                ->requiresConfirmation()
                ->action(fn () => $this->record->update(['completed_at' => now()])),
            DeleteAction::make()
                ->modalHeading(__('reminders.headings.delete'))
                ->modalDescription(fn () => __('reminders.headings.delete_description', ['title' => $this->record->title])),
        ];
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
