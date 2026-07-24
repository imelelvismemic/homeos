<?php

namespace App\Modules\Tasks\Filament\Resources\TaskResource\Pages;

use App\Modules\Tasks\Filament\Resources\TaskResource;
use App\Modules\Tasks\Services\RecurrenceService;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateTask extends CreateRecord
{
    protected static string $resource = TaskResource::class;

    public function getTitle(): string
    {
        return __('tasks.headings.create');
    }

    public function getBreadcrumb(): string
    {
        return __('tasks.headings.create');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // household_id vežemo na trenutni Filament tenant; created_by na korisnika.
        // Ownership se postavlja preko relacije na zapisu (Task->household), pa
        // Household (Platform) ne mora imati relaciju ka modulu (§4).
        $data['household_id'] = Filament::getTenant()?->getKey();
        $data['created_by'] = auth()->id();
        $data['recurrence_rule'] = RecurrenceService::ruleFromChoice($this->data['recurrence'] ?? 'none');

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        // Direktan create (household_id je već postavljen) — zaobilazi Filamentovo
        // vezivanje preko $tenant->tasks(), koje bi zahtijevalo relaciju na Householdu.
        return static::getModel()::create($data);
    }

    protected function afterCreate(): void
    {
        $this->record->syncTags($this->data['tags'] ?? []);
    }
}
