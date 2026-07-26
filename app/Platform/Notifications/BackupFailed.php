<?php

namespace App\Platform\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Noćni backup nije uspio (ROADMAP Faza 8). Tehničko upozorenje — ide na adresu
 * iz `HOMEOS_ALERT_EMAIL`, ne u korisničko sanduče, i nema kategoriju koju bi
 * član mogao isključiti.
 */
class BackupFailed extends Notification
{
    public function __construct(public string $reason) {}

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
            ->error()
            ->subject(__('platform.backup.failed_subject', ['app' => config('homeos.name')]))
            ->line(__('platform.backup.failed_line'))
            ->line($this->reason)
            ->line(__('platform.backup.failed_hint'));
    }
}
