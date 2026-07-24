<?php

use App\Platform\Filament\QuickCapture;
use App\Platform\QuickCapture\QuickCaptureRegistry;
use Filament\Facades\Filament;
use Livewire\Livewire;

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

it('resolves capture hrefs (with tenant segment) at mount so they survive tenant-less updates', function () {
    // Route-name url za panel rutu koja traži {tenant}. Prije ispravke,
    // href se računao pri otvaranju modala (Livewire update bez tenant
    // konteksta) i ispadao bez {tenant} segmenta → 404.
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

    $items = Livewire::test(QuickCapture::class)->get('items');

    expect($items)->toHaveCount(1);
    expect($items[0]['href'])->toContain("/{$household->getRouteKey()}/tasks/create");
});
