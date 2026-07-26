<?php

namespace App\Modules\Pets\Filament\Resources\PetResource\Pages;

use App\Modules\Pets\Filament\Resources\PetResource;
use App\Platform\Filament\Concerns\CreatesForCurrentHousehold;
use Filament\Resources\Pages\CreateRecord;

class CreatePet extends CreateRecord
{
    use CreatesForCurrentHousehold;

    protected static string $resource = PetResource::class;

    public function getTitle(): string
    {
        return __('pets.headings.create');
    }

    public function getBreadcrumb(): string
    {
        return __('pets.headings.create');
    }
}
