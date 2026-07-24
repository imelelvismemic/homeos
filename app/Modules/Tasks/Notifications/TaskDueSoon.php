<?php

namespace App\Modules\Tasks\Notifications;

use App\Modules\Tasks\Filament\Resources\TaskResource;
use App\Modules\Tasks\Models\Task;
use App\Platform\Notifications\HouseholdNotification;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Zadatak uskoro ističe (DATA_MODEL.md §5 kategorija `task_due_soon`).
 * Šalje ga centralni scheduler (routes/schedule.php) odgovornoj osobi.
 */
class TaskDueSoon extends HouseholdNotification
{
    public function __construct(public Task $task) {}

    public function category(): string
    {
        return 'task_due_soon';
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('tasks.notifications.due_soon.subject'))
            ->line(__('tasks.notifications.due_soon.line', [
                'title' => $this->task->title,
                'when' => $this->task->due_date?->diffForHumans() ?? '',
            ]))
            ->action(__('tasks.notifications.due_soon.action'), TaskResource::getUrl('edit', ['record' => $this->task]));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'category' => $this->category(),
            'task_id' => $this->task->getKey(),
            'title' => $this->task->title,
            'due_date' => $this->task->due_date?->toIso8601String(),
        ];
    }
}
