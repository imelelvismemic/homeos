<?php

namespace App\Platform\QuickCapture;

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
 * Core ne zna za module — samo iterira registrovane stavke. Modal (Alpine + fetch)
 * renderuje tipove i polja odavde; generički QuickCreateController koristi handler.
 * Graceful sa 0 modula.
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
        return collect(config('homeos-apps', []))
            ->filter(fn (array $app) => ($app['enabled'] ?? true) && ! empty($app['quick_capture']['handler']))
            ->map(fn (array $app, string $key) => [
                'key' => $key,
                'label' => $app['quick_capture']['label'] ?? ($app['name'] ?? $key),
                'icon' => $app['quick_capture']['icon'] ?? $app['icon'] ?? null,
                'fields' => array_values($app['quick_capture']['fields'] ?? []),
            ])
            ->values();
    }

    /** Handler klasa za dati modul (ili null ako nije registrovan/isključen). */
    public function handlerClassFor(string $key): ?string
    {
        $app = config("homeos-apps.{$key}");

        if (! is_array($app) || ! ($app['enabled'] ?? true)) {
            return null;
        }

        return $app['quick_capture']['handler'] ?? null;
    }

    /**
     * Validaciona pravila za dati modul (iz handlera).
     *
     * @return array<string, mixed>
     */
    public function rulesFor(string $key): array
    {
        $handler = $this->handlerClassFor($key);

        return $handler ? app($handler)->rules() : [];
    }
}
