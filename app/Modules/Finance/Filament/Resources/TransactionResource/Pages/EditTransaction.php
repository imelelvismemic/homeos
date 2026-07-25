<?php

namespace App\Modules\Finance\Filament\Resources\TransactionResource\Pages;

use App\Modules\Finance\Filament\Resources\TransactionResource;
use App\Platform\Filament\Sharing\SharingForm;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTransaction extends EditRecord
{
    protected static string $resource = TransactionResource::class;

    public function getTitle(): string
    {
        return __('finance.transactions.headings.edit');
    }

    protected function getHeaderActions(): array
    {
        return [
            SharingForm::pageAction(),
            DeleteAction::make()
                ->modalHeading(__('finance.transactions.delete'))
                ->modalDescription(fn () => __('finance.transactions.delete_description', ['title' => $this->record->title])),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['participants'] = $this->record->participants()->pluck('household_members.id')->all();

        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->participants()->sync($this->data['participants'] ?? []);
    }
}
