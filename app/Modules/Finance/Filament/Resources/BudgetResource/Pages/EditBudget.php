<?php

namespace App\Modules\Finance\Filament\Resources\BudgetResource\Pages;

use App\Modules\Finance\Filament\Resources\BudgetResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBudget extends EditRecord
{
    protected static string $resource = BudgetResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
