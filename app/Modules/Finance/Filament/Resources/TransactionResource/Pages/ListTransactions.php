<?php

namespace App\Modules\Finance\Filament\Resources\TransactionResource\Pages;

use App\Modules\Finance\Filament\Resources\TransactionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label(__('finance.transactions.actions.create'))];
    }
}
