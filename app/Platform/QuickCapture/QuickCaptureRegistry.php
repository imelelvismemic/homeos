<?php

namespace App\Platform\QuickCapture;

use App\Platform\Modules\ModuleRegistry;
use Illuminate\Support\Collection;

/**
 * Brzo dodavanje (ROADMAP Faza 2.4) — proširiv registry. Modul u
 * config/homeos-apps.php doda `quick_capture`:
 *
 *     'quick_capture' => [
 *         'label' => 'Novi zadatak',
 *         'icon' => 'heroicon-o-check-circle',
 *         'handler' => \App\Modules\Tasks\QuickCapture\TaskQuickCreate::class,
 *         'fields' => [
 *             ['name' => 'title', 'label' => 'Naslov', 'type' => 'text', 'required' => true],
 *         ],
 *     ],
 *
 * Modul koji nudi VIŠE tipova brzog unosa (Finansije: trošak i račun) daje listu
 * definicija, svaku sa svojim `key`:
 *
 *     'quick_capture' => [
 *         ['key' => 'expense', 'label' => 'Novi trošak', 'handler' => …, 'fields' => …],
 *         ['key' => 'bill',    'label' => 'Novi račun',  'handler' => …, 'fields' => …],
 *     ],
 *
 * Javni ključ je tada `finance.expense` / `finance.bill`; modul s jednim tipom
 * ostaje samo `tasks`. Core ne zna za module — iterira registrovane stavke; modal
 * (Alpine + fetch) renderuje tipove i polja odavde, a generički QuickCreateController
 * koristi handler. Graceful sa 0 modula.
 *
 * Podržani tipovi polja: `text`, `textarea`, `number`, `date`, `datetime`.
 */
class QuickCaptureRegistry
{
    /**
     * Stavke za UI modala (bez handlera — on je server-side).
     *
     * @return Collection<int, array{key: string, label: string, icon: ?string, fields: array<int, array<string, mixed>>}>
     */
    public function items(): Collection
    {
        return app(ModuleRegistry::class)->enabled()
            ->flatMap(fn (array $app, string $moduleKey) => collect($this->definitions($app))
                ->filter(fn (array $definition) => ! empty($definition['handler']))
                ->map(fn (array $definition) => [
                    'key' => $this->keyFor($moduleKey, $app, $definition),
                    'label' => $definition['label'] ?? ($app['name'] ?? $moduleKey),
                    'icon' => $definition['icon'] ?? $app['icon'] ?? null,
                    'fields' => array_values($definition['fields'] ?? []),
                ])
                ->values())
            ->values();
    }

    /** Handler klasa za dati ključ (ili null ako nije registrovan/isključen). */
    public function handlerClassFor(string $key): ?string
    {
        [$moduleKey, $subKey] = array_pad(explode('.', $key, 2), 2, null);

        $app = config("homeos-apps.{$moduleKey}");

        if (! is_array($app) || ! app(ModuleRegistry::class)->isEnabled($moduleKey)) {
            return null;
        }

        foreach ($this->definitions($app) as $definition) {
            if (($definition['key'] ?? null) === $subKey) {
                return $definition['handler'] ?? null;
            }
        }

        return null;
    }

    /**
     * Validaciona pravila za dati ključ (iz handlera).
     *
     * @return array<string, mixed>
     */
    public function rulesFor(string $key): array
    {
        $handler = $this->handlerClassFor($key);

        return $handler ? app($handler)->rules() : [];
    }

    /**
     * Definicije brzog unosa modula — uvijek kao lista (jedna ili više).
     *
     * @param  array<string, mixed>  $app
     * @return array<int, array<string, mixed>>
     */
    private function definitions(array $app): array
    {
        $quickCapture = $app['quick_capture'] ?? null;

        if (! is_array($quickCapture)) {
            return [];
        }

        return array_is_list($quickCapture) ? $quickCapture : [$quickCapture];
    }

    /**
     * @param  array<string, mixed>  $app
     * @param  array<string, mixed>  $definition
     */
    private function keyFor(string $moduleKey, array $app, array $definition): string
    {
        return array_is_list($app['quick_capture'] ?? [])
            ? $moduleKey.'.'.($definition['key'] ?? '')
            : $moduleKey;
    }
}
