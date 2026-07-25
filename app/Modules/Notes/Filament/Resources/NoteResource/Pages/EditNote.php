<?php

namespace App\Modules\Notes\Filament\Resources\NoteResource\Pages;

use App\Modules\Notes\Filament\Resources\NoteResource;
use App\Platform\Filament\Sharing\SharingForm;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditNote extends EditRecord
{
    protected static string $resource = NoteResource::class;

    public function getTitle(): string
    {
        return __('notes.headings.edit');
    }

    protected function getHeaderActions(): array
    {
        return [
            SharingForm::pageAction(),
            DeleteAction::make()
                ->modalHeading(__('notes.headings.delete'))
                ->modalDescription(fn () => __('notes.headings.delete_description', ['title' => $this->record->displayTitle()])),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['tags'] = $this->record->tagNames();

        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->syncTags($this->data['tags'] ?? []);
    }
}
