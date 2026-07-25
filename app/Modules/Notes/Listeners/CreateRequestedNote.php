<?php

namespace App\Modules\Notes\Listeners;

use App\Modules\Notes\Models\Note;
use App\Platform\Events\NoteRequested;
use App\Platform\Sharing\VisibilityMirror;

/**
 * Auto-discoveran listener (CLAUDE.md §9): kreira bilješku vezanu za entitet koji
 * je drugi modul "prijavio" preko NoteRequested eventa. Notes ne importuje taj
 * modul — radi generički s Model instancom iz eventa.
 */
class CreateRequestedNote
{
    public function handle(NoteRequested $event): void
    {
        $notable = $event->notable;

        $note = Note::create([
            'household_id' => $notable->household_id,
            'created_by' => auth()->id() ?? $notable->created_by,
            'title' => $event->title,
            'body' => $event->body,
            'notable_type' => $notable->getMorphClass(),
            'notable_id' => $notable->getKey(),
        ]);

        // Bilješka nasljeđuje vidljivost izvora (privatan izvor → privatna bilješka).
        VisibilityMirror::mirror($notable, $note);
    }
}
