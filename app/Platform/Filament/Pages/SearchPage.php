<?php

namespace App\Platform\Filament\Pages;

use App\Platform\Models\Household;
use App\Platform\Search\SearchResult;
use App\Platform\Search\SearchService;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Univerzalna pretraga (ROADMAP Faza 1.5 — "command palette / global search").
 * Agregira rezultate SVIH registrovanih modula preko platforme
 * (App\Platform\Search\SearchService, ključ `search_provider` u
 * config/homeos-apps.php) — core ne zna pojedinačno za module (CLAUDE.md §8/§18).
 *
 * Prava Filament stranica (ne render-hook Livewire), pa update zahtjevi nose
 * tenant kontekst i pretraga je household-scoped bez zaobilaženja.
 */
class SearchPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-magnifying-glass';

    protected static ?string $slug = 'pretraga';

    protected static string $view = 'filament.platform.pages.search';

    protected static ?int $navigationSort = -1;

    public string $q = '';

    public static function getNavigationLabel(): string
    {
        return __('search.title');
    }

    public function getTitle(): string
    {
        return __('search.title');
    }

    public function mount(): void
    {
        // Ulaz iz topbar trake: /pretraga?q=...
        $this->q = (string) request()->query('q', '');
    }

    public function hasQuery(): bool
    {
        return Str::length(trim($this->q)) >= 2;
    }

    /**
     * @return Collection<string, Collection<int, SearchResult>>
     */
    public function getGroupedResults(): Collection
    {
        $tenant = Filament::getTenant();

        if (! $tenant instanceof Household || ! $this->hasQuery()) {
            return collect();
        }

        return app(SearchService::class)->searchGrouped(trim($this->q), $tenant);
    }

    /** Prikazni naziv grupe rezultata (npr. 'task' → "Zadaci"). */
    public function typeLabel(string $type): string
    {
        $key = "search.types.{$type}";

        return __($key) === $key ? Str::headline($type) : __($key);
    }
}
