<?php

namespace App\Modules\Notes\Listeners;

use App\Modules\Notes\Models\Note;
use App\Platform\Events\VisibilityChanged;
use App\Platform\Sharing\VisibilityMirror;

/**
 * Kad se promijeni vidljivost nekog Shareable objekta (VisibilityChanged), bilješka
 * izvedena iz njega (notable) usklađuje svoju vidljivost. Notes sluša generički
 * event, ne zna za izvorni modul (CLAUDE.md §9/§11).
 */
class SyncNotableVisibility
{
    public function handle(VisibilityChanged $event): void
    {
        $source = $event->shareable;

        // Sama bilješka nije ničiji notable — izbjegni suvišan upit/rekurziju.
        if ($source instanceof Note) {
            return;
        }

        Note::query()
            ->where('notable_type', $source->getMorphClass())
            ->where('notable_id', $source->getKey())
            ->get()
            ->each(fn (Note $note) => VisibilityMirror::mirror($source, $note));
    }
}
