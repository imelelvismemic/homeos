<?php

namespace App\Modules\LifeAdmin\Filament\Resources\DocumentResource\Pages;

use App\Modules\LifeAdmin\Filament\Resources\DocumentResource;
use App\Platform\Filament\Sharing\SharingForm;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDocument extends EditRecord
{
    protected static string $resource = DocumentResource::class;

    public function getTitle(): string
    {
        return __('lifeadmin.documents.headings.edit');
    }

    protected function getHeaderActions(): array
    {
        return [
            SharingForm::pageAction(),
            DeleteAction::make()
                ->modalHeading(__('lifeadmin.documents.delete'))
                ->modalDescription(fn () => __('lifeadmin.documents.delete_description', ['title' => $this->record->title])),
        ];
    }
}
