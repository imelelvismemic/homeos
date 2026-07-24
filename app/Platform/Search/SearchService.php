<?php

namespace App\Platform\Search;

use App\Platform\Contracts\SearchProviderContract;
use App\Platform\Models\Household;
use Illuminate\Support\Collection;

/**
 * Agregira rezultate svih registrovanih search providera (CLAUDE.md §8).
 * Providere čita ISKLJUČIVO iz config/homeos-apps.php (`search_provider` ključ,
 * CLAUDE.md §12) — ne zna pojedinačno za module. Modul postaje pretraživ tako
 * što se registruje u tom configu; ovdje se ništa ne mijenja.
 *
 * Graceful: sa 0 registrovanih modula vraća praznu kolekciju, ne baca grešku.
 */
class SearchService
{
    /**
     * @return Collection<int, SearchResult>
     */
    public function search(string $query, Household $household): Collection
    {
        if (trim($query) === '') {
            return collect();
        }

        return collect(config('homeos-apps', []))
            ->filter(fn (array $app) => ($app['enabled'] ?? true) && ! empty($app['search_provider']))
            ->map(fn (array $app) => app($app['search_provider']))
            ->filter(fn ($provider) => $provider instanceof SearchProviderContract)
            ->flatMap(fn (SearchProviderContract $provider) => $provider->search($query, $household))
            ->values();
    }

    /**
     * Rezultati grupisani po tipu (za prikaz u UI / komandnoj paleti).
     *
     * @return Collection<string, Collection<int, SearchResult>>
     */
    public function searchGrouped(string $query, Household $household): Collection
    {
        return $this->search($query, $household)->groupBy(fn (SearchResult $r) => $r->type);
    }
}
