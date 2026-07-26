<?php

namespace App\Modules\Pets\Filament\Resources\PetResource\Pages;

use App\Modules\Pets\Filament\Resources\PetResource;
use App\Platform\Filament\Concerns\CancelReturnsToList;
use App\Platform\Filament\Sharing\SharingForm;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPet extends EditRecord
{
    use CancelReturnsToList;

    protected static string $resource = PetResource::class;

    public function getTitle(): string
    {
        return __('pets.headings.edit');
    }

    protected function getHeaderActions(): array
    {
        return [
            SharingForm::pageAction(),
            DeleteAction::make()
                ->modalHeading(__('pets.headings.delete'))
                ->modalDescription(fn () => __('pets.headings.delete_description', ['name' => $this->record->name])),
        ];
    }
}
