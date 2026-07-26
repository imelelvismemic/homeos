<?php

namespace App\Modules\Pets\Filament\Resources\PetResource\Pages;

use App\Modules\Pets\Filament\Resources\PetResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPets extends ListRecords
{
    protected static string $resource = PetResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label(__('pets.headings.create'))];
    }
}
