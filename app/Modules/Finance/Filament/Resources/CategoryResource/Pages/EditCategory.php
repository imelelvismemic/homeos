<?php

namespace App\Modules\Finance\Filament\Resources\CategoryResource\Pages;

use App\Modules\Finance\Filament\Resources\CategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

    public function getTitle(): string
    {
        return __('finance.categories.headings.edit');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->modalHeading(__('finance.categories.delete'))
                ->modalDescription(fn () => __('finance.categories.delete_description', ['name' => $this->record->name])),
        ];
    }
}
