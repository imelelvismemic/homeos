<?php

namespace App\Modules\Notes\Filament\Resources\NoteResource\Pages;

use App\Modules\Notes\Filament\Resources\NoteResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateNote extends CreateRecord
{
    protected static string $resource = NoteResource::class;

    public function getTitle(): string
    {
        return __('notes.headings.create');
    }

    public function getBreadcrumb(): string
    {
        return __('notes.headings.create');
    }

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

    protected function afterCreate(): void
    {
        $this->record->syncTags($this->data['tags'] ?? []);
    }
}
