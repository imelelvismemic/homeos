<?php

namespace App\Platform\Search;

use App\Platform\Contracts\SearchProviderContract;
use App\Platform\Models\Household;
use App\Platform\Models\HouseholdMember;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Članovi domaćinstva u univerzalnoj pretrazi. Ovo je provider SAME PLATFORME
 * (član nije modul), pa ga SearchService uključuje uvijek — registry u
 * config/homeos-apps.php i dalje ostaje jedini izvor za module (CLAUDE.md §12).
 *
 * Rezultat je ograničen na trenutno odabrano domaćinstvo i vodi na Postavke
 * domaćinstva, gdje je lista članova.
 */
class MemberSearchProvider implements SearchProviderContract
{
    public function search(string $query, Household $household): Collection
    {
        return HouseholdMember::query()
            ->where('household_id', $household->getKey())
            ->whereHas('user', fn (Builder $q) => $q
                ->where('name', 'like', "%{$query}%")
                ->orWhere('email', 'like', "%{$query}%"))
            ->with('user')
            ->limit(8)
            ->get()
            ->map(fn (HouseholdMember $member) => new SearchResult(
                type: 'member',
                id: $member->getKey(),
                title: $member->user?->name ?? $member->user?->email ?? '—',
                // Imenovana ruta (ne Page::getUrl) — URL se gradi i kad Filament
                // "current panel"/tenant kontekst nije postavljen, npr. u fetch-u
                // iz command palette. Isti obrazac kao ruta avatara.
                url: route('filament.app.tenant.profile', ['tenant' => $household]),
                icon: 'heroicon-o-user-circle',
            ));
    }

    public function type(): string
    {
        return 'member';
    }
}
