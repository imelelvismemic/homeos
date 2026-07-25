<?php

namespace App\Modules\LifeAdmin\Filament\Resources\ShoppingListResource\Pages;

use App\Modules\LifeAdmin\Filament\Resources\ShoppingListResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListShoppingLists extends ListRecords
{
    protected static string $resource = ShoppingListResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label(__('lifeadmin.lists.headings.create'))];
    }
}
