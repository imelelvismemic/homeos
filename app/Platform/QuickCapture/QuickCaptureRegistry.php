<?php

namespace App\Platform\QuickCapture;

use Illuminate\Support\Collection;

/**
 * Brzo dodavanje (ROADMAP Faza 2.4) — proširiv registry, isti obrazac kao
 * dashboard widgeti i search. Modul u config/homeos-apps.php doda `quick_capture`:
 *
 *     'quick_capture' => [
 *         'label' => 'Novi zadatak',
 *         'icon' => 'heroicon-o-check-circle',
 *         'url' => 'filament.app.resources.tasks.create', // route name ili URL
 *     ],
 *
 * Core ne zna za module — samo iterira registrovane stavke. Graceful sa 0 modula.
 */
class QuickCaptureRegistry
{
    /**
     * @return Collection<int, array{key: string, label: string, icon: ?string, url: ?string}>
     */
    public function items(): Collection
    {
        return collect(config('homeos-apps', []))
            ->filter(fn (array $app) => ($app['enabled'] ?? true) && ! empty($app['quick_capture']))
            ->map(fn (array $app, string $key) => [
                'key' => $key,
                'label' => $app['quick_capture']['label'] ?? ($app['name'] ?? $key),
                'icon' => $app['quick_capture']['icon'] ?? $app['icon'] ?? null,
                'url' => $app['quick_capture']['url'] ?? null,
            ])
            ->values();
    }
}
