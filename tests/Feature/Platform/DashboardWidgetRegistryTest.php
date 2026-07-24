<?php

use App\Platform\Dashboard\DashboardWidgetRegistry;
use Tests\Fixtures\EmptyDashboardWidget;
use Tests\Fixtures\FakeDashboardWidget;

it('returns nothing when no modules are registered (graceful)', function () {
    config()->set('homeos-apps', []);
    [$household] = makeHousehold();

    $registry = app(DashboardWidgetRegistry::class);

    expect($registry->widgetClassesFor($household))->toBe([]);
    expect($registry->activeTitlesFor($household))->toBe([]);
});

it('aggregates a registered widget that has content', function () {
    config()->set('homeos-apps', [
        'tasks' => ['enabled' => true, 'dashboard_widget' => FakeDashboardWidget::class],
    ]);
    [$household] = makeHousehold();

    $registry = app(DashboardWidgetRegistry::class);

    expect($registry->widgetClassesFor($household))->toBe(['App\\Fake\\TasksWidget']);
    expect($registry->activeTitlesFor($household))->toBe(['Zadaci']);
});

it('omits widgets without content and disabled modules', function () {
    config()->set('homeos-apps', [
        'bills' => ['enabled' => true, 'dashboard_widget' => EmptyDashboardWidget::class],
        'tasks' => ['enabled' => false, 'dashboard_widget' => FakeDashboardWidget::class],
    ]);
    [$household] = makeHousehold();

    $registry = app(DashboardWidgetRegistry::class);

    // EmptyDashboardWidget nema sadržaj; tasks je isključen → oba izostaju.
    expect($registry->widgetClassesFor($household))->toBe([]);
    expect($registry->activeTitlesFor($household))->toBe([]);
});
