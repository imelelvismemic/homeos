<?php

namespace App\Modules\Finance\Filament\Resources\BudgetResource\Pages;

use App\Modules\Finance\Filament\Resources\BudgetResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBudgets extends ListRecords
{
    protected static string $resource = BudgetResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label(__('finance.budgets.actions.create'))];
    }
}
