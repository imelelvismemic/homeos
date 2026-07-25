<?php

namespace App\Modules\LifeAdmin\Filament\Resources\ShoppingListResource\Pages;

use App\Modules\LifeAdmin\Filament\Resources\ShoppingListResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditShoppingList extends EditRecord
{
    protected static string $resource = ShoppingListResource::class;

    public function getTitle(): string
    {
        return __('lifeadmin.lists.headings.edit');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->modalHeading(__('lifeadmin.lists.delete'))
                ->modalDescription(fn () => __('lifeadmin.lists.delete_description', ['name' => $this->record->name])),
        ];
    }
}
