<?php

namespace App\Modules\Finance\Filament\Resources\BillResource\Pages;

use App\Modules\Finance\Filament\Resources\BillResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBills extends ListRecords
{
    protected static string $resource = BillResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label(__('finance.bills.actions.create'))];
    }
}
