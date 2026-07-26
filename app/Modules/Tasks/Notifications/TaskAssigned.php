<?php

namespace App\Modules\Tasks\Notifications;

use App\Modules\Tasks\Filament\Resources\TaskResource;
use App\Modules\Tasks\Models\Task;
use App\Platform\Notifications\HouseholdNotification;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Zadatak je dodijeljen članu (DATA_MODEL.md §5 kategorija `task_assigned`).
 * Okida ga TaskAssigned event preko NotifyAssignee listenera.
 */
class TaskAssigned extends HouseholdNotification
{
    public function __construct(public Task $task) {}

    public function category(): string
    {
        return 'task_assigned';
    }

    /**
     * Tenant se prosljeđuje EKSPLICITNO: obavještenja nastaju i u konzoli
     * (scheduler), gdje Filament nema "trenutno domaćinstvo" — bez toga getUrl()
     * puca na nedostajućem {tenant} parametru, pa email nikad ne ode iako je
     * in-app obavijest već upisana (database kanal ide prvi).
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('tasks.notifications.assigned.subject'))
            ->line(__('tasks.notifications.assigned.line', ['title' => $this->task->title]))
            ->action(__('tasks.notifications.assigned.action'), TaskResource::getUrl('edit', ['record' => $this->task, 'tenant' => $this->task->household_id]));
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
        ];
    }
}
