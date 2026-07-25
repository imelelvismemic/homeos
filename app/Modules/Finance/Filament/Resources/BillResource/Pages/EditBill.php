<?php

namespace App\Modules\Finance\Filament\Resources\BillResource\Pages;

use App\Modules\Finance\Filament\Resources\BillResource;
use App\Platform\Filament\Sharing\SharingForm;
use App\Platform\Recurrence\RecurrenceService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBill extends EditRecord
{
    protected static string $resource = BillResource::class;

    public function getTitle(): string
    {
        return __('finance.bills.headings.edit');
    }

    protected function getHeaderActions(): array
    {
        return [
            SharingForm::pageAction(),
            Action::make('markPaid')
                ->label(__('finance.bills.actions.mark_paid'))
                ->icon('heroicon-m-check')
                ->color('success')
                ->visible(fn () => ! $this->record->isPaid())
                ->requiresConfirmation()
                ->action(fn () => $this->record->update(['paid_at' => now()])),
            DeleteAction::make()
                ->modalHeading(__('finance.bills.delete'))
                ->modalDescription(fn () => __('finance.bills.delete_description', ['title' => $this->record->title])),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['recurrence'] = RecurrenceService::choiceFromRule($this->record->recurrence_rule);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['recurrence_rule'] = RecurrenceService::ruleFromChoice($this->data['recurrence'] ?? 'none');

        return $data;
    }
}
