<?php

use App\Modules\Finance\Models\Bill;
use App\Modules\Finance\Models\Transaction;
use App\Modules\Notes\Models\Note;
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

it('provides a QuickCreateContract handler for every registered capture option', function () {
    $registry = app(QuickCaptureRegistry::class);

    expect($registry->items())->not->toBeEmpty();

    foreach ($registry->items() as $item) {
        expect(app($registry->handlerClassFor($item['key'])))->toBeInstanceOf(QuickCreateContract::class);
    }
});

it('lets a module register several capture options under its own keys', function () {
    $keys = app(QuickCaptureRegistry::class)->items()->pluck('key');

    // Finansije nude i trošak i račun (QuickCaptureRegistry: lista definicija).
    expect($keys)->toContain('finance.expense');
    expect($keys)->toContain('finance.bill');
    expect($keys)->toContain('tasks');
});

it('quick-creates a bill with its minimum required fields', function () {
    [$household, $owner] = makeHousehold();

    $url = route('filament.app.quick-create', ['key' => 'finance.bill', 'h' => $household->getKey()]);

    test()->actingAs($owner->user)
        ->postJson($url, ['title' => 'Struja', 'amount' => '80.50', 'due_date' => '2026-08-10'])
        ->assertOk();

    $bill = Bill::firstWhere('title', 'Struja');

    expect($bill)->not->toBeNull();
    expect($bill->household_id)->toBe($household->id);
    expect($bill->due_date->toDateString())->toBe('2026-08-10');
    expect($bill->remind_days_before)->toBe(3);
});

it('validates the bill quick-create input', function () {
    [$household, $owner] = makeHousehold();

    $url = route('filament.app.quick-create', ['key' => 'finance.bill', 'h' => $household->getKey()]);

    test()->actingAs($owner->user)
        ->postJson($url, ['title' => 'Bez iznosa'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['amount', 'due_date']);
});

it('carries the day picked on the calendar into each kind of quick-created record', function () {
    [$household, $owner] = makeHousehold();
    $picked = '2026-08-14 09:00';

    $post = fn (string $key, array $payload) => test()->actingAs($owner->user)->postJson(
        route('filament.app.quick-create', ['key' => $key, 'h' => $household->getKey()]),
        $payload + ['date' => $picked],
    )->assertOk();

    $post('tasks', ['title' => 'Zadatak s kalendara']);
    $post('notes', ['body' => 'Zapis s kalendara']);
    $post('finance.expense', ['title' => 'Trošak s kalendara', 'amount' => '12.00']);

    // Zadatak: izabrani dan je rok.
    expect(Task::firstWhere('title', 'Zadatak s kalendara')->due_date->toDateString())->toBe('2026-08-14');
    // Bilješka: izabrani dan je datum dnevnika.
    expect(Note::query()->whereNotNull('journal_date')->first()->journal_date->toDateString())->toBe('2026-08-14');
    // Trošak: izabrani dan, ne današnji.
    expect(Transaction::firstWhere('title', 'Trošak s kalendara')->date->toDateString())->toBe('2026-08-14');
});
