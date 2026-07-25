<?php

namespace App\Modules\Tasks\QuickCapture;

use App\Models\User;
use App\Modules\Tasks\Enums\Priority;
use App\Modules\Tasks\Enums\TaskStatus;
use App\Modules\Tasks\Models\Task;
use App\Platform\Models\Household;
use App\Platform\QuickCapture\QuickCreateContract;

class TaskQuickCreate implements QuickCreateContract
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            // Datum kliknut na kalendaru (opciono).
            'date' => ['nullable', 'date'],
        ];
    }

    public function create(array $data, Household $household, User $user): void
    {
        Task::create([
            'household_id' => $household->getKey(),
            'created_by' => $user->getKey(),
            'title' => $data['title'],
            // Dodavanje s kalendara: kliknuti dan je rok zadatka.
            'due_date' => $data['date'] ?? null,
            'priority' => Priority::Medium,
            'status' => TaskStatus::Todo,
        ]);
    }
}
