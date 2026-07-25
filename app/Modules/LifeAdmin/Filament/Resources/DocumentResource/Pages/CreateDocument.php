<?php

namespace App\Modules\LifeAdmin\Filament\Resources\DocumentResource\Pages;

use App\Modules\LifeAdmin\Filament\Resources\DocumentResource;
use App\Platform\Filament\Concerns\CreatesForCurrentHousehold;
use Filament\Resources\Pages\CreateRecord;

class CreateDocument extends CreateRecord
{
    use CreatesForCurrentHousehold;

    protected static string $resource = DocumentResource::class;

    public function getTitle(): string
    {
        return __('lifeadmin.documents.headings.create');
    }

    public function getBreadcrumb(): string
    {
        return __('lifeadmin.documents.headings.create');
    }
}
