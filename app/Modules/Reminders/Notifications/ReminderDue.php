<?php

namespace App\Modules\Reminders\Notifications;

use App\Modules\Reminders\Filament\Resources\ReminderResource;
use App\Modules\Reminders\Models\Reminder;
use App\Platform\Notifications\HouseholdNotification;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Podsjetnik je okinuo (DATA_MODEL.md §5 kategorija `reminder_fired`).
 * Šalje ga scheduler preko ReminderFired eventa.
 */
class ReminderDue extends HouseholdNotification
{
    public function __construct(public Reminder $reminder) {}

    public function category(): string
    {
        return 'reminder_fired';
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('reminders.notifications.due.subject'))
            ->line(__('reminders.notifications.due.line', ['title' => $this->reminder->title]))
            ->action(__('reminders.notifications.due.action'), ReminderResource::getUrl('edit', ['record' => $this->reminder]));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'category' => $this->category(),
            'reminder_id' => $this->reminder->getKey(),
            'title' => $this->reminder->title,
            'due_date' => $this->reminder->due_date?->toIso8601String(),
        ];
    }
}
