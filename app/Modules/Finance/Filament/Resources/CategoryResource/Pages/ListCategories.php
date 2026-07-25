<?php

namespace App\Modules\Finance\Filament\Resources\CategoryResource\Pages;

use App\Modules\Finance\Filament\Resources\CategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label(__('finance.categories.actions.create'))];
    }
}
