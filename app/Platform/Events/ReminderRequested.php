<?php

namespace App\Platform\Events;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Generički platform event: neki modul traži da se kreira podsjetnik vezan za
 * jedan od njegovih entiteta (CLAUDE.md §9). Modul koji emituje NE zna za modul
 * Podsjetnici, niti obrnuto — Reminders sluša ovaj event i kreira Reminder s
 * polimorfnom vezom (`remindable`). Tako se podsjetnik veže za postojeći entitet
 * kroz javni interfejs, bez direktnog pristupa tuđoj bazi (DoD Faze 4).
 */
class ReminderRequested
{
    use Dispatchable;

    public function __construct(
        public Model $remindable,
        public CarbonInterface $dueDate,
        public string $title,
        public ?string $description = null,
        public ?int $assignedTo = null,   // HouseholdMember id (opciono)
    ) {}
}
