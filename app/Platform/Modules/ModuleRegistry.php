<?php

namespace App\Platform\Modules;

use App\Platform\Models\Household;
use App\Platform\Models\HouseholdModule;
use Filament\Facades\Filament;
use Illuminate\Support\Collection;

/**
 * Jedinstveni odgovor na pitanje "koji su moduli dostupni ovom domaćinstvu"
 * (ROADMAP Faza 7, CLAUDE.md §12).
 *
 * Izvor liste modula je i dalje ISKLJUČIVO `config/homeos-apps.php`. Ovdje se
 * na to nakalemi izbor domaćinstva iz tabele `household_modules`, koja čuva samo
 * ODSTUPANJA — domaćinstvo koje ništa nije mijenjalo dobija `enabled` iz configa,
 * pa novi modul odmah radi svima bez migracije podataka.
 *
 * Svi registri platforme (dashboard, pretraga, kalendar, digest, brzo dodavanje,
 * kategorije obavještenja) i navigacija pitaju OVAJ servis — nigdje se više ne
 * čita `$app['enabled']` direktno, jer bi tada isključivanje radilo samo dijelom.
 */
class ModuleRegistry
{
    /** @var array<int, array<string, bool>> keširano po domaćinstvu unutar zahtjeva */
    private array $overrides = [];

    /**
     * Svi moduli iz registryja, bez obzira na uključenost.
     *
     * @return Collection<string, array<string, mixed>>
     */
    public function all(): Collection
    {
        return collect(config('homeos-apps', []))->filter(fn ($app) => is_array($app));
    }

    /**
     * Moduli dostupni datom domaćinstvu (bez domaćinstva: samo default iz configa).
     *
     * @return Collection<string, array<string, mixed>>
     */
    public function enabled(?Household $household = null): Collection
    {
        $household ??= $this->currentHousehold();

        return $this->all()->filter(fn (array $app, string $key) => $this->isEnabled($key, $household));
    }

    public function isEnabled(string $key, ?Household $household = null): bool
    {
        $app = config("homeos-apps.{$key}");

        if (! is_array($app)) {
            return false;
        }

        $household ??= $this->currentHousehold();
        $default = (bool) ($app['enabled'] ?? true);

        if (! $household instanceof Household) {
            return $default;
        }

        return $this->overridesFor($household)[$key] ?? $default;
    }

    /** Upiši izbor domaćinstva (samo ako se razlikuje od defaulta — inače obriši red). */
    public function setEnabled(Household $household, string $key, bool $enabled): void
    {
        if (! is_array(config("homeos-apps.{$key}"))) {
            return;
        }

        HouseholdModule::updateOrCreate(
            ['household_id' => $household->getKey(), 'module_key' => $key],
            ['enabled' => $enabled],
        );

        unset($this->overrides[$household->getKey()]);
    }

    /**
     * Ključ modula iz imena klase, po konvenciji foldera (App\Modules\<Ime>\…).
     * Tako core zna kojem modulu pripada neki Filament Resource/Page, a da ne drži
     * listu klasa po modulu (CLAUDE.md §4/§5 — auto-discovery po folderu).
     */
    public function keyForClass(string $class): ?string
    {
        if (! preg_match('/^App\\\\Modules\\\\([^\\\\]+)\\\\/', $class, $matches)) {
            return null;
        }

        $key = strtolower($matches[1]);

        return is_array(config("homeos-apps.{$key}")) ? $key : null;
    }

    /** Da li je modul kojem klasa pripada dostupan trenutnom domaćinstvu. */
    public function classIsEnabled(string $class): bool
    {
        $key = $this->keyForClass($class);

        // Klasa izvan modula (npr. platformska stranica) se nikad ne gasi.
        return $key === null || $this->isEnabled($key);
    }

    /**
     * @return array<string, bool>
     */
    private function overridesFor(Household $household): array
    {
        return $this->overrides[$household->getKey()] ??= HouseholdModule::query()
            ->where('household_id', $household->getKey())
            ->pluck('enabled', 'module_key')
            ->map(fn ($enabled) => (bool) $enabled)
            ->all();
    }

    private function currentHousehold(): ?Household
    {
        $tenant = Filament::getTenant();

        return $tenant instanceof Household ? $tenant : null;
    }
}
