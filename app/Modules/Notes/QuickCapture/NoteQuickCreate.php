<?php

namespace App\Modules\Notes\QuickCapture;

use App\Models\User;
use App\Modules\Notes\Models\Note;
use App\Platform\Models\Household;
use App\Platform\QuickCapture\QuickCreateContract;

class NoteQuickCreate implements QuickCreateContract
{
    public function rules(): array
    {
        return [
            'body' => ['required', 'string'],
        ];
    }

    public function create(array $data, Household $household, User $user): void
    {
        // Brzi unos je običan tekst; puno uređivanje (rich text) je u resource-u.
        Note::create([
            'household_id' => $household->getKey(),
            'created_by' => $user->getKey(),
            'body' => '<p>'.e($data['body']).'</p>',
        ]);
    }
}
