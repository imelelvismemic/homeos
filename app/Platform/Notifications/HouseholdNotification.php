<?php

namespace App\Platform\Notifications;

use App\Platform\Models\HouseholdMember;
use Illuminate\Notifications\Notification;

/**
 * Zajednička osnova za sva obavještenja u sistemu (CLAUDE.md §10). Modul NIKAD
 * ne šalje email direktno (Mail::send) — pravi notifikaciju koja nasljeđuje ovu
 * klasu, pa korisničko uključivanje/isključivanje email kategorija (Faza 6)
 * radi automatski.
 *
 * Pravilo kanala:
 *  - `database` (in-app) se šalje UVIJEK,
 *  - `mail` se šalje samo ako član nije isključio ovu kategoriju
 *    (NotificationPreference.email_enabled; default uključeno ako nema zapisa).
 *
 * Notifiable je HouseholdMember.
 */
abstract class HouseholdNotification extends Notification
{
    /** Kategorija obavještenja (DATA_MODEL.md §5), npr. 'shared_with_you'. */
    abstract public function category(): string;

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if ($this->wantsEmail($notifiable)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    protected function wantsEmail(HouseholdMember $member): bool
    {
        $preference = $member->notificationPreferences()
            ->where('category', $this->category())
            ->first();

        // Bez eksplicitnog zapisa — email je uključen po defaultu.
        return $preference?->email_enabled ?? true;
    }
}
