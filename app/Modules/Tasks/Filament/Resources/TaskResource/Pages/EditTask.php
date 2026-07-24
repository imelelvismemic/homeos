<?php

namespace App\Modules\Tasks\Filament\Resources\TaskResource\Pages;

use App\Modules\Tasks\Filament\Resources\TaskResource;
use App\Modules\Tasks\Services\RecurrenceService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTask extends EditRecord
{
    protected static string $resource = TaskResource::class;

    public function getTitle(): string
    {
        return __('tasks.headings.edit');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->modalHeading(__('tasks.headings.delete'))
                ->modalDescription(fn () => __('tasks.headings.delete_description', ['title' => $this->record->title])),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['tags'] = $this->record->tagNames();
        $data['recurrence'] = RecurrenceService::choiceFromRule($this->record->recurrence_rule);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['recurrence_rule'] = RecurrenceService::ruleFromChoice($this->data['recurrence'] ?? 'none');

        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->syncTags($this->data['tags'] ?? []);
    }
}
