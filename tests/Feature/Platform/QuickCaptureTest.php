<?php

use App\Modules\Tasks\Models\Task;
use App\Modules\Tasks\QuickCapture\TaskQuickCreate;
use App\Platform\QuickCapture\QuickCaptureRegistry;
use App\Platform\QuickCapture\QuickCreateContract;
use Filament\Facades\Filament;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('app'));
});

it('has no capture options when no modules are registered', function () {
    config()->set('homeos-apps', []);

    expect(app(QuickCaptureRegistry::class)->items())->toBeEmpty();
});

it('exposes capture options (label/icon/fields) registered by a module', function () {
    config()->set('homeos-apps', [
        'tasks' => [
            'enabled' => true,
            'name' => 'Zadaci',
            'quick_capture' => [
                'label' => 'Novi zadatak',
                'icon' => 'heroicon-o-check-circle',
                'handler' => TaskQuickCreate::class,
                'fields' => [
                    ['name' => 'title', 'label' => 'Naslov', 'type' => 'text', 'required' => true],
                ],
            ],
        ],
    ]);

    $items = app(QuickCaptureRegistry::class)->items();

    expect($items)->toHaveCount(1);
    expect($items->first()['label'])->toBe('Novi zadatak');
    expect($items->first()['fields'][0]['name'])->toBe('title');
    expect(app(QuickCaptureRegistry::class)->handlerClassFor('tasks'))
        ->toBe(TaskQuickCreate::class);
});

it('quick-creates a task via the endpoint, scoped to the household', function () {
    [$household, $owner] = makeHousehold();

    $url = route('filament.app.quick-create', ['key' => 'tasks', 'h' => $household->getKey()]);

    test()->actingAs($owner->user)
        ->postJson($url, ['title' => 'Brzi zadatak'])
        ->assertOk()
        ->assertJson(['ok' => true]);

    $task = Task::firstWhere('title', 'Brzi zadatak');
    expect($task)->not->toBeNull();
    expect($task->household_id)->toBe($household->id);
    expect($task->created_by)->toBe($owner->user_id);
});

it('validates quick-create input (422 on missing required field)', function () {
    [$household, $owner] = makeHousehold();

    $url = route('filament.app.quick-create', ['key' => 'tasks', 'h' => $household->getKey()]);

    test()->actingAs($owner->user)
        ->postJson($url, ['title' => ''])
        ->assertStatus(422)
        ->assertJsonValidationErrors('title');
});

it('rejects quick-create for a household the user is not a member of', function () {
    [$householdA] = makeHousehold();
    [, $ownerB] = makeHousehold();

    $url = route('filament.app.quick-create', ['key' => 'tasks', 'h' => $householdA->getKey()]);

    test()->actingAs($ownerB->user)->postJson($url, ['title' => 'X'])->assertStatus(404);
});

it('rejects quick-create when unauthenticated', function () {
    [$householdA] = makeHousehold();

    $url = route('filament.app.quick-create', ['key' => 'tasks', 'h' => $householdA->getKey()]);

    test()->postJson($url, ['title' => 'X'])->assertStatus(403);
});

it('provides a QuickCreateContract handler for each module that registers quick capture', function () {
    foreach (config('homeos-apps') as $key => $app) {
        if (empty($app['quick_capture']['handler'])) {
            continue;
        }

        expect(app($app['quick_capture']['handler']))->toBeInstanceOf(QuickCreateContract::class);
    }
});
