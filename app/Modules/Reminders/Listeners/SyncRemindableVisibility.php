<?php

namespace App\Modules\Reminders\Listeners;

use App\Modules\Reminders\Models\Reminder;
use App\Platform\Events\VisibilityChanged;
use App\Platform\Sharing\VisibilityMirror;

/**
 * Kad se promijeni vidljivost nekog Shareable objekta (VisibilityChanged), podsjetnik
 * izveden iz njega (remindable) usklađuje svoju vidljivost — privatan račun povlači
 * privatan podsjetnik i obrnuto. Reminders sluša generički event, ne zna za izvorni
 * modul (CLAUDE.md §9/§11).
 */
class SyncRemindableVisibility
{
    public function handle(VisibilityChanged $event): void
    {
        $source = $event->shareable;

        // Sam podsjetnik nije ničiji remindable — izbjegni suvišan upit/rekurziju.
        if ($source instanceof Reminder) {
            return;
        }

        Reminder::query()
            ->where('remindable_type', $source->getMorphClass())
            ->where('remindable_id', $source->getKey())
            ->get()
            ->each(fn (Reminder $reminder) => VisibilityMirror::mirror($source, $reminder));
    }
}
