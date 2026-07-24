<?php

use App\Platform\QuickCapture\QuickCaptureRegistry;

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
