<?php

namespace App\Modules\Finance\Filament\Resources\BudgetResource\Pages;

use App\Modules\Finance\Filament\Resources\BudgetResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBudget extends EditRecord
{
    protected static string $resource = BudgetResource::class;

    public function getTitle(): string
    {
        return __('finance.budgets.headings.edit');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->modalHeading(__('finance.budgets.delete'))
                ->modalDescription(fn () => __('finance.budgets.delete_description', [
                    'category' => $this->record->category?->name ?? '—',
                    'month' => $this->record->month?->translatedFormat('F Y.') ?? '',
                ])),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Select opcije su 'Y-m-d' stringovi prvog dana mjeseca — uskladi vrijednost.
        $data['month'] = $this->record->month?->toDateString();

        return $data;
    }
}
