<?php

namespace App\Modules\Finance\Filament\Resources\CategoryResource\Pages;

use App\Modules\Finance\Filament\Resources\CategoryResource;
use App\Platform\Filament\Concerns\CreatesForCurrentHousehold;
use Filament\Resources\Pages\CreateRecord;

class CreateCategory extends CreateRecord
{
    use CreatesForCurrentHousehold;

    protected static string $resource = CategoryResource::class;
}
