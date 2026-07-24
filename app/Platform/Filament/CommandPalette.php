<?php

namespace App\Platform\Filament;

use App\Platform\Models\Household;
use App\Platform\Search\SearchResult;
use App\Platform\Search\SearchService;
use Filament\Facades\Filament;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Component;

/**
 * Univerzalna pretraga kao command palette (Ctrl/Cmd+K). Renderuje se u topbaru
 * preko render hooka, dostupna sa svake stranice i na svim širinama.
 *
 * Agregira SVE registrovane module preko SearchService-a (ključ `search_provider`
 * u config/homeos-apps.php) — core ne zna pojedinačno za module (CLAUDE.md §8/§18).
 *
 * Otvaranje/zatvaranje i fokus rješava Alpine (client-side), pa nema Livewire
 * round-tripa za UI. Livewire samo prima `q` (kao search box tabele) i renderuje
 * rezultate. Tenant se pamti kao householdId u mount()-u, jer /livewire/update
 * ne prolazi kroz panel tenant middleware (getTenant() bi bio null).
 */
class CommandPalette extends Component
{
    public string $q = '';

    public int $householdId = 0;

    public function mount(): void
    {
        $this->householdId = (int) (Filament::getTenant()?->getKey() ?? 0);
    }

    /**
     * Boot se izvršava na SVAKOM zahtjevu (mount i /livewire/update). Custom
     * Livewire komponenta u render hooku ne prolazi Filamentov serving lifecycle
     * na update-u, pa "current panel" i tenant nisu postavljeni → TaskResource::getUrl()
     * baca TypeError koji Livewire u produkciji pretvori u tihi 419. Ovdje
     * eksplicitno uspostavljamo Filament kontekst da rezultati i URL-ovi rade.
     */
    public function boot(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('app'));

        $household = $this->householdId ? Household::find($this->householdId) : null;

        if ($household && auth()->check()) {
            Filament::setTenant($household);
        }
    }

    public function hasQuery(): bool
    {
        return Str::length(trim($this->q)) >= 2;
    }

    /**
     * @return Collection<string, Collection<int, SearchResult>>
     */
    public function groupedResults(): Collection
    {
        $household = $this->householdId ? Household::find($this->householdId) : null;

        // Bez korisnika (visibleTo zahtijeva User) ili domaćinstva — prazno, ne pucaj.
        if (! $household || ! auth()->check() || ! $this->hasQuery()) {
            return collect();
        }

        return app(SearchService::class)->searchGrouped(trim($this->q), $household);
    }

    /** Prikazni naziv grupe rezultata (npr. 'task' → "Zadaci"). */
    public function typeLabel(string $type): string
    {
        $key = "search.types.{$type}";

        return __($key) === $key ? Str::headline($type) : __($key);
    }

    public function render(): View
    {
        return view('filament.platform.command-palette', [
            'groups' => $this->groupedResults(),
        ]);
    }
}
