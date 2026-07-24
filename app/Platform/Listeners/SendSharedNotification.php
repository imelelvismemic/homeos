<?php

namespace App\Platform\Listeners;

use App\Platform\Events\Shared;
use App\Platform\Notifications\SharedWithYou;

/**
 * Auto-discoveran listener (CLAUDE.md §9): kad se objekat podijeli sa određenim
 * članovima (Shared event), pošalje im `shared_with_you` obavještenje. Ovo je
 * primjer kako platform povezuje event → notifikaciju; modul koji koristi
 * Shareable::shareWith() dobija ovo bez ijedne svoje linije.
 */
class SendSharedNotification
{
    public function handle(Shared $event): void
    {
        foreach ($event->recipients as $member) {
            $member->notify(new SharedWithYou($event->shareable));
        }
    }
}
