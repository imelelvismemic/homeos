<?php

namespace App\Modules\LifeAdmin\Filament\Resources\DocumentResource\Pages;

use App\Modules\LifeAdmin\Filament\Resources\DocumentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDocuments extends ListRecords
{
    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label(__('lifeadmin.documents.headings.create'))];
    }
}
