<?php

namespace App\Modules\LifeAdmin\Filament\Resources\ContactResource\Pages;

use App\Modules\LifeAdmin\Filament\Resources\ContactResource;
use App\Platform\Filament\Concerns\CreatesForCurrentHousehold;
use Filament\Resources\Pages\CreateRecord;

class CreateContact extends CreateRecord
{
    use CreatesForCurrentHousehold;

    protected static string $resource = ContactResource::class;

    public function getTitle(): string
    {
        return __('lifeadmin.contacts.headings.create');
    }

    public function getBreadcrumb(): string
    {
        return __('lifeadmin.contacts.headings.create');
    }
}
