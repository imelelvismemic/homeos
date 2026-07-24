<?php

namespace App\Modules\Notes\Filament\Resources\NoteResource\Pages;

use App\Modules\Notes\Filament\Resources\NoteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListNotes extends ListRecords
{
    protected static string $resource = NoteResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label(__('notes.actions.create'))];
    }
}
