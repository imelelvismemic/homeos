<?php

namespace App\Modules\Tasks\Filament\Resources\TaskResource\Pages;

use App\Modules\Tasks\Filament\Resources\TaskResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTasks extends ListRecords
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
