<?php

namespace App\Modules\Notes\Events;

use App\Modules\Notes\Models\Note;
use Illuminate\Foundation\Events\Dispatchable;

class NoteCreated
{
    use Dispatchable;

    public function __construct(public Note $note) {}
}
