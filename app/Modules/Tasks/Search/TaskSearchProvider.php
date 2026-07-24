<?php

namespace App\Modules\Tasks\Search;

use App\Modules\Tasks\Filament\Resources\TaskResource;
use App\Modules\Tasks\Models\Task;
use App\Platform\Contracts\SearchProviderContract;
use App\Platform\Models\Household;
use App\Platform\Search\SearchResult;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TaskSearchProvider implements SearchProviderContract
{
    public function search(string $query, Household $household): Collection
    {
        return Task::query()
            ->where('household_id', $household->id)
            ->visibleTo(auth()->user())
            ->where(function (Builder $q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%")
                    // Sadržaj oznaka (dijeljeni platform tagovi).
                    ->orWhereHas('tags', fn ($t) => $t->where('name', 'like', "%{$query}%"))
                    // Ime i prezime odgovorne osobe.
                    ->orWhereHas('assignee.user', fn ($u) => $u->where('name', 'like', "%{$query}%"));
            })
            ->limit(8)
            ->get()
            ->map(fn (Task $task) => new SearchResult(
                type: 'task',
                id: $task->id,
                title: $task->title,
                url: TaskResource::getUrl('edit', ['record' => $task]),
                icon: 'heroicon-o-check-circle',
            ));
    }

    public function type(): string
    {
        return 'task';
    }
}
