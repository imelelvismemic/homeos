<?php

namespace App\Modules\Notes\Search;

use App\Modules\Notes\Filament\Resources\NoteResource;
use App\Modules\Notes\Models\Note;
use App\Platform\Contracts\SearchProviderContract;
use App\Platform\Models\Household;
use App\Platform\Search\SearchResult;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class NoteSearchProvider implements SearchProviderContract
{
    public function search(string $query, Household $household): Collection
    {
        return Note::query()
            ->where('household_id', $household->id)
            ->visibleTo(auth()->user())
            ->where(function (Builder $q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('body', 'like', "%{$query}%");
            })
            ->limit(8)
            ->get()
            ->map(fn (Note $note) => new SearchResult(
                type: 'note',
                id: $note->id,
                title: $note->displayTitle(),
                url: NoteResource::getUrl('edit', ['record' => $note, 'tenant' => $household]),
                icon: 'heroicon-o-document-text',
            ));
    }

    public function type(): string
    {
        return 'note';
    }
}
