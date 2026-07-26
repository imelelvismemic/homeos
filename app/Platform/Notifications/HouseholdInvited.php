<?php

namespace App\Platform\Notifications;

use App\Platform\Models\HouseholdInvitation;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Pozivnica u domaćinstvo (ROADMAP Faza 7c).
 *
 * NE nasljeđuje HouseholdNotification: primalac još nema nalog ni članstvo, pa
 * nema ni in-app sanduče ni postavke kategorija — ovo je jedino email, poslan
 * "on-demand" na adresu. I dalje ide kroz Notification sistem, ne `Mail::send`
 * (CLAUDE.md §10).
 */
class HouseholdInvited extends Notification
{
    public function __construct(
        public HouseholdInvitation $invitation,
        public string $token,
        public string $householdName,
        public string $inviterName,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('platform.invitations.mail.subject', ['household' => $this->householdName]))
            ->greeting(__('platform.invitations.mail.greeting'))
            ->line(__('platform.invitations.mail.line', [
                'inviter' => $this->inviterName,
                'household' => $this->householdName,
                'app' => config('homeos.name'),
            ]))
            ->action(__('platform.invitations.mail.action'), route('household-invitation', ['token' => $this->token]))
            ->line(__('platform.invitations.mail.expires', [
                'days' => $this->invitation->expires_at->diffInDays(now()) + 1,
            ]))
            ->salutation(__('platform.invitations.mail.salutation', ['app' => config('homeos.name')]));
    }
}
