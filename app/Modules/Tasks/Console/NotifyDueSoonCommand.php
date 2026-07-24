<?php

namespace App\Modules\Tasks\Console;

use App\Modules\Tasks\Models\Task;
use App\Modules\Tasks\Notifications\TaskDueSoon;
use Illuminate\Console\Command;

/**
 * Obavještava odgovorne osobe o zadacima koji uskoro ističu (kategorija
 * `task_due_soon`). Pokreće ga centralni scheduler jednom dnevno; prozor je
 * narednih 24h, pa jedan dnevni prolaz daje tačno jedno obavještenje po zadatku.
 */
class NotifyDueSoonCommand extends Command
{
    protected $signature = 'tasks:notify-due-soon';

    protected $description = 'Obavijesti odgovorne osobe o zadacima koji ističu u narednih 24 sata';

    public function handle(): int
    {
        $tasks = Task::query()
            ->whereNull('completed_at')
            ->whereNotNull('assigned_to')
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [now(), now()->addDay()])
            ->with('assignee')
            ->get();

        $sent = 0;

        foreach ($tasks as $task) {
            if ($task->assignee === null) {
                continue;
            }

            $task->assignee->notify(new TaskDueSoon($task));
            $sent++;
        }

        $this->info("Poslano obavještenja: {$sent}");

        return self::SUCCESS;
    }
}
