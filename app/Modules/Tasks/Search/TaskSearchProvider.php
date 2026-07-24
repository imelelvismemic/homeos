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
        // Univerzalna pretraga ide po vlastitom tekstu zadatka (naslov + opis).
        // Pretraga po odgovornoj osobi i oznakama je namjerno u search boxu SAME
        // liste zadataka (TaskResource tabela), ne ovdje.
        return Task::query()
            ->where('household_id', $household->id)
            ->visibleTo(auth()->user())
            ->where(function (Builder $q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            })
            ->limit(8)
            ->get()
            ->map(fn (Task $task) => new SearchResult(
                type: 'task',
                id: $task->id,
                title: $task->title,
                // Tenant eksplicitno — URL se gradi i tokom Livewire update-a
                // (command palette) gdje Filament tenant kontekst nije postavljen.
                url: TaskResource::getUrl('edit', ['record' => $task, 'tenant' => $household]),
                icon: 'heroicon-o-check-circle',
            ));
    }

    public function type(): string
    {
        return 'task';
    }
}
