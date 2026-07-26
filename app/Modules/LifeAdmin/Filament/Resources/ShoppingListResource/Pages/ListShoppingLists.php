<?php

namespace App\Modules\LifeAdmin\Filament\Resources\ShoppingListResource\Pages;

use App\Modules\LifeAdmin\Filament\Resources\ShoppingListResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListShoppingLists extends ListRecords
{
    protected static string $resource = ShoppingListResource::class;

    // Naslov više ne treba override — rečeničnu kapitalizaciju nosi zajednička
    // osnova ModuleResource ($hasTitleCaseModelLabel = false), pa vrijedi za sve
    // module odjednom (docs/PRAVILA.md §2).
    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label(__('lifeadmin.lists.headings.create'))];
    }
}
