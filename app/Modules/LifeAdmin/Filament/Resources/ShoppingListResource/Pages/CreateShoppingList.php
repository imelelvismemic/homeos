<?php

namespace App\Modules\LifeAdmin\Filament\Resources\ShoppingListResource\Pages;

use App\Modules\LifeAdmin\Filament\Resources\ShoppingListResource;
use App\Platform\Filament\Concerns\CreatesForCurrentHousehold;
use Filament\Resources\Pages\CreateRecord;

class CreateShoppingList extends CreateRecord
{
    use CreatesForCurrentHousehold;

    protected static string $resource = ShoppingListResource::class;

    public function getTitle(): string
    {
        return __('lifeadmin.lists.headings.create');
    }

    public function getBreadcrumb(): string
    {
        return __('lifeadmin.lists.headings.create');
    }
}
