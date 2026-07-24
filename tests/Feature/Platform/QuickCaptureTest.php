<?php

use App\Platform\QuickCapture\QuickCaptureRegistry;
use Filament\Facades\Filament;
use Illuminate\Support\Str;

it('has no capture options when no modules are registered', function () {
    config()->set('homeos-apps', []);

    expect(app(QuickCaptureRegistry::class)->items())->toBeEmpty();
});

it('exposes a capture option registered by a module', function () {
    config()->set('homeos-apps', [
        'tasks' => [
            'enabled' => true,
            'name' => 'Zadaci',
            'icon' => 'heroicon-o-check-circle',
            'quick_capture' => [
                'label' => 'Novi zadatak',
                'url' => 'https://homeos.test/tasks/create',
            ],
        ],
    ]);

    $items = app(QuickCaptureRegistry::class)->items();

    expect($items)->toHaveCount(1);
    expect($items->first()['label'])->toBe('Novi zadatak');
    expect($items->first()['icon'])->toBe('heroicon-o-check-circle');
    expect($items->first()['url'])->toBe('https://homeos.test/tasks/create');
});

it('resolves a panel-route capture href with the tenant segment at page render', function () {
    // "Brzo dodaj" je običan dropdown linkova renderovan u topbar render hooku
    // (bez Livewire modala) — href se razrješava dok je tenant kontekst dostupan.
    // Ranije je route() padao bez {tenant} segmenta → 404/419 na /livewire/update.
    config()->set('homeos-apps', [
        'tasks' => [
            'enabled' => true,
            'name' => 'Zadaci',
            'icon' => 'heroicon-o-check-circle',
            'quick_capture' => [
                'label' => 'Novi zadatak',
                'url' => 'filament.app.resources.tasks.create',
            ],
        ],
    ]);

    Filament::setCurrentPanel(Filament::getPanel('app'));
    [$household, $owner] = makeHousehold();
    test()->actingAs($owner->user);
    Filament::setTenant($household);

    // Replicira logiku render hooka (HomePanelProvider).
    $tenant = Filament::getTenant();
    $items = app(QuickCaptureRegistry::class)->items()
        ->map(function (array $item) use ($tenant): array {
            $item['href'] = Str::startsWith($item['url'], ['http', '/'])
                ? $item['url']
                : route($item['url'], $tenant ? ['tenant' => $tenant] : []);

            return $item;
        });

    $html = view('filament.platform.quick-capture', ['items' => $items])->render();

    expect($html)->toContain("/{$household->getRouteKey()}/tasks/create");
    expect($html)->toContain('Novi zadatak');
});
