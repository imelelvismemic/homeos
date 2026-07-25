<?php

namespace App\Modules\LifeAdmin\Filament\Resources\ContactResource\Pages;

use App\Modules\LifeAdmin\Filament\Resources\ContactResource;
use App\Platform\Filament\Sharing\SharingForm;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditContact extends EditRecord
{
    protected static string $resource = ContactResource::class;

    public function getTitle(): string
    {
        return __('lifeadmin.contacts.headings.edit');
    }

    protected function getHeaderActions(): array
    {
        return [
            SharingForm::pageAction(),
            DeleteAction::make()
                ->modalHeading(__('lifeadmin.contacts.delete'))
                ->modalDescription(fn () => __('lifeadmin.contacts.delete_description', ['name' => $this->record->name])),
        ];
    }
}
