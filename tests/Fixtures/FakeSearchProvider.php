<?php

namespace Tests\Fixtures;

use App\Platform\Contracts\SearchProviderContract;
use App\Platform\Models\Household;
use App\Platform\Search\SearchResult;
use Illuminate\Support\Collection;

/**
 * Simulira search provider koji bi modul registrovao u config/homeos-apps.php.
 * Koristi se u SearchTest da dokaže agregaciju bez izmjene core-a.
 */
class FakeSearchProvider implements SearchProviderContract
{
    public function search(string $query, Household $household): Collection
    {
        return collect([
            new SearchResult(type: 'fake', id: 1, title: "rezultat: {$query}"),
        ]);
    }

    public function type(): string
    {
        return 'fake';
    }
}
