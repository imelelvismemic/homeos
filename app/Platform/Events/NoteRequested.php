<?php

namespace App\Platform\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Generički platform event: neki modul traži da se kreira bilješka vezana za
 * jedan od njegovih entiteta (analogno ReminderRequested, CLAUDE.md §9). Notes
 * sluša i kreira Note s polimorfnom `notable` vezom — bez cross-module importa
 * ni direktnog pristupa tuđoj bazi (DoD Faze 4).
 */
class NoteRequested
{
    use Dispatchable;

    public function __construct(
        public Model $notable,
        public string $body,
        public ?string $title = null,
    ) {}
}
