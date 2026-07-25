<?php

namespace App\Modules\Finance\Filament\Resources\TransactionResource\Pages;

use App\Modules\Finance\Filament\Resources\TransactionResource;
use App\Platform\Filament\Concerns\CreatesForCurrentHousehold;
use Filament\Resources\Pages\CreateRecord;

class CreateTransaction extends CreateRecord
{
    use CreatesForCurrentHousehold;

    protected static string $resource = TransactionResource::class;

    public function getTitle(): string
    {
        return __('finance.transactions.headings.create');
    }

    public function getBreadcrumb(): string
    {
        return __('finance.transactions.headings.create');
    }

    protected function afterCreate(): void
    {
        $this->record->participants()->sync($this->data['participants'] ?? []);
    }
}
