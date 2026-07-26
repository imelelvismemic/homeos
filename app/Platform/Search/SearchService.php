<?php

namespace App\Platform\Search;

use App\Platform\Contracts\SearchProviderContract;
use App\Platform\Models\Household;
use App\Platform\Modules\ModuleRegistry;
use Illuminate\Support\Collection;

/**
 * Agregira rezultate svih registrovanih search providera (CLAUDE.md §8).
 * MODULE čita ISKLJUČIVO iz config/homeos-apps.php (`search_provider` ključ,
 * CLAUDE.md §12) — ne zna pojedinačno za module. Modul postaje pretraživ tako
 * što se registruje u tom configu; ovdje se ništa ne mijenja.
 *
 * Uz njih uvijek idu i provideri SAME PLATFORME (npr. članovi domaćinstva) —
 * to nisu moduli, ne mogu se isključiti i ne pripadaju registryju.
 *
 * Graceful: sa 0 registrovanih modula vraća samo platformske rezultate.
 */
class SearchService
{
    /**
     * Provideri koje nosi sama platforma (nisu moduli iz registryja).
     *
     * @var array<int, class-string<SearchProviderContract>>
     */
    private const CORE_PROVIDERS = [
        MemberSearchProvider::class,
    ];

    /**
     * @return Collection<int, SearchResult>
     */
    public function search(string $query, Household $household): Collection
    {
        if (trim($query) === '') {
            return collect();
        }

        return app(ModuleRegistry::class)->enabled($household)
            ->filter(fn (array $app) => ! empty($app['search_provider']))
            ->map(fn (array $app) => app($app['search_provider']))
            ->merge(collect(self::CORE_PROVIDERS)->map(fn (string $provider) => app($provider)))
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
