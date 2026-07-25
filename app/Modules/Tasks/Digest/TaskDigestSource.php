<?php

namespace App\Modules\Tasks\Digest;

use App\Models\User;
use App\Modules\Tasks\Enums\TaskStatus;
use App\Modules\Tasks\Models\Task;
use App\Platform\Contracts\DigestSourceContract;
use App\Platform\Digest\DigestSection;
use App\Platform\Models\Household;
use Carbon\CarbonInterface;

/**
 * Doprinos Zadataka digestu (Faza 6): zadaci s rokom u periodu koje član smije
 * vidjeti i koji nisu završeni.
 */
class TaskDigestSource implements DigestSourceContract
{
    public function digestSection(Household $household, User $user, CarbonInterface $from, CarbonInterface $to): ?DigestSection
    {
        $tasks = Task::query()
            ->where('household_id', $household->id)
            ->visibleTo($user)
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$from, $to])
            ->where('status', '!=', TaskStatus::Done->value)
            ->orderBy('due_date')
            ->get();

        if ($tasks->isEmpty()) {
            return null;
        }

        return new DigestSection(
            __('tasks.plural_label'),
            $tasks->map(fn (Task $t) => '• '.$t->title.' — '.$t->due_date->translatedFormat('d.m. H:i'))->all(),
        );
    }
}
