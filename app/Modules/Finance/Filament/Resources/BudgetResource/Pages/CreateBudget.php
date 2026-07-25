<?php

namespace App\Modules\Finance\Filament\Resources\BudgetResource\Pages;

use App\Modules\Finance\Filament\Resources\BudgetResource;
use App\Platform\Filament\Concerns\CreatesForCurrentHousehold;
use Filament\Resources\Pages\CreateRecord;

class CreateBudget extends CreateRecord
{
    use CreatesForCurrentHousehold;

    protected static string $resource = BudgetResource::class;
}
