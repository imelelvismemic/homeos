<?php

namespace App\Platform\Contracts;

use App\Platform\Models\Household;
use App\Platform\Search\SearchResult;
use Illuminate\Support\Collection;

/**
 * Svaki modul koji želi biti pretraživ implementira ovaj interfejs i registruje
 * ga u config/homeos-apps.php pod ključem `search_provider`. Core search
 * (App\Platform\Search\SearchService) agregira rezultate svih registrovanih
 * providera — ne zna pojedinačno za module (CLAUDE.md tačka 8).
 */
interface SearchProviderContract
{
    /**
     * Vraća kolekciju rezultata za dati upit, ograničeno na dato domaćinstvo.
     * Svaki rezultat je App\Platform\Search\SearchResult (id, title, url, icon).
     *
     * @return Collection<int, SearchResult>
     */
    public function search(string $query, Household $household): Collection;

    /** Ključ tipa rezultata, npr. 'task', 'note' — za grupisanje u UI. */
    public function type(): string;
}
