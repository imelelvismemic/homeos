<?php

namespace App\Modules\LifeAdmin\Filament\Resources\ShoppingListResource\Pages;

use App\Modules\LifeAdmin\Filament\Resources\ShoppingListResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListShoppingLists extends ListRecords
{
    protected static string $resource = ShoppingListResource::class;

    // Filament default heading title-case-uje plural label ("Liste Za Kupovinu").
    // Bosanski je rečenična kapitalizacija (PRAVILA.md) — koristimo label kako jeste.
    public function getTitle(): string
    {
        return __('lifeadmin.lists.plural_label');
    }

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label(__('lifeadmin.lists.headings.create'))];
    }
}
