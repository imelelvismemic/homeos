<?php

namespace App\Modules\Pets\Search;

use App\Modules\Pets\Filament\Resources\PetResource;
use App\Modules\Pets\Models\Pet;
use App\Platform\Contracts\SearchProviderContract;
use App\Platform\Models\Household;
use App\Platform\Search\SearchResult;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Ljubimci u univerzalnoj pretrazi — po vlastitom tekstu zapisa (ime + bilješka),
 * kako nalaže docs/PRAVILA.md §8.
 */
class PetSearchProvider implements SearchProviderContract
{
    public function search(string $query, Household $household): Collection
    {
        return Pet::query()
            ->where('household_id', $household->id)
            ->visibleTo(auth()->user())
            ->where(function (Builder $q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('notes', 'like', "%{$query}%");
            })
            ->limit(8)
            ->get()
            ->map(fn (Pet $pet) => new SearchResult(
                type: 'pet',
                id: $pet->id,
                title: $pet->name,
                url: PetResource::getUrl('edit', ['record' => $pet, 'tenant' => $household]),
                icon: 'heroicon-o-heart',
            ));
    }

    public function type(): string
    {
        return 'pet';
    }
}
