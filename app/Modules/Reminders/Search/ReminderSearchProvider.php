<?php

namespace App\Modules\Reminders\Search;

use App\Modules\Reminders\Filament\Resources\ReminderResource;
use App\Modules\Reminders\Models\Reminder;
use App\Platform\Contracts\SearchProviderContract;
use App\Platform\Models\Household;
use App\Platform\Search\SearchResult;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ReminderSearchProvider implements SearchProviderContract
{
    public function search(string $query, Household $household): Collection
    {
        return Reminder::query()
            ->where('household_id', $household->id)
            ->visibleTo(auth()->user())
            ->where(function (Builder $q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            })
            ->limit(8)
            ->get()
            ->map(fn (Reminder $reminder) => new SearchResult(
                type: 'reminder',
                id: $reminder->id,
                title: $reminder->title,
                url: ReminderResource::getUrl('edit', ['record' => $reminder, 'tenant' => $household]),
                icon: 'heroicon-o-bell',
            ));
    }

    public function type(): string
    {
        return 'reminder';
    }
}
