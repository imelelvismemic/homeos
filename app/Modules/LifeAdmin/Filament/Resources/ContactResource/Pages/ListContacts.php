<?php

namespace App\Modules\LifeAdmin\Filament\Resources\ContactResource\Pages;

use App\Modules\LifeAdmin\Filament\Resources\ContactResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListContacts extends ListRecords
{
    protected static string $resource = ContactResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label(__('lifeadmin.contacts.headings.create'))];
    }
}
