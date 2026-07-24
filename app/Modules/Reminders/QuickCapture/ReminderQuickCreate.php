<?php

namespace App\Modules\Reminders\QuickCapture;

use App\Models\User;
use App\Modules\Reminders\Models\Reminder;
use App\Platform\Models\Household;
use App\Platform\QuickCapture\QuickCreateContract;

class ReminderQuickCreate implements QuickCreateContract
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'due_date' => ['required', 'date'],
        ];
    }

    public function create(array $data, Household $household, User $user): void
    {
        Reminder::create([
            'household_id' => $household->getKey(),
            'created_by' => $user->getKey(),
            'title' => $data['title'],
            'due_date' => $data['due_date'],
        ]);
    }
}
