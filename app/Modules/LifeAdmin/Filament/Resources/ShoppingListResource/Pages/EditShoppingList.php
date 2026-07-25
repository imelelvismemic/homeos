<?php

namespace App\Modules\LifeAdmin\Filament\Resources\ShoppingListResource\Pages;

use App\Modules\LifeAdmin\Filament\Resources\ShoppingListResource;
use App\Platform\Filament\Concerns\CancelReturnsToList;
use App\Platform\Filament\Sharing\SharingForm;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditShoppingList extends EditRecord
{
    use CancelReturnsToList;

    protected static string $resource = ShoppingListResource::class;

    public function getTitle(): string
    {
        return __('lifeadmin.lists.headings.edit');
    }

    protected function getHeaderActions(): array
    {
        return [
            SharingForm::pageAction(),
            DeleteAction::make()
                ->modalHeading(__('lifeadmin.lists.delete'))
                ->modalDescription(fn () => __('lifeadmin.lists.delete_description', ['name' => $this->record->name])),
        ];
    }
}
