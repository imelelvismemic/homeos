<?php

use App\Models\User;
use App\Modules\Finance\Filament\Resources\BillResource;
use App\Modules\Tasks\Filament\Resources\TaskResource;
use App\Modules\Tasks\Models\Task;
use App\Platform\Calendar\CalendarService;
use App\Platform\Dashboard\DashboardWidgetRegistry;
use App\Platform\Filament\Pages\Dashboard;
use App\Platform\Filament\Tenancy\EditHouseholdProfile;
use App\Platform\Models\HouseholdModule;
use App\Platform\Modules\ModuleRegistry;
use App\Platform\Notifications\NotificationCategoryRegistry;
use App\Platform\QuickCapture\QuickCaptureRegistry;
use App\Platform\Search\SearchService;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('app'));
});

it('uses the config default until the household says otherwise', function () {
    [$household] = makeHousehold();
    $registry = app(ModuleRegistry::class);

    expect($registry->isEnabled('finance', $household))->toBeTrue();
    expect($household->hasMany(HouseholdModule::class)->count())->toBe(0);

    $registry->setEnabled($household, 'finance', false);

    expect($registry->isEnabled('finance', $household))->toBeFalse();
    // Drugo domaćinstvo nije dirano — postavka je po domaćinstvu.
    [$other] = makeHousehold();
    expect($registry->isEnabled('finance', $other))->toBeTrue();
});

it('drops a disabled module out of every aggregated surface', function () {
    [$household, $owner] = makeHousehold();
    test()->actingAs($owner->user);
    Filament::setTenant($household);

    Task::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'title' => 'Zadatak koji nestaje',
        'due_date' => now()->addHour(),
    ]);

    // Prije gašenja: zadatak je svuda.
    expect(app(SearchService::class)->search('nestaje', $household)->pluck('type'))->toContain('task');
    expect(app(DashboardWidgetRegistry::class)->activeTitlesFor($household))->not->toBeEmpty();
    expect(app(CalendarService::class)->eventsBetween(now()->subDay(), now()->addDay(), $household))->not->toBeEmpty();
    expect(app(QuickCaptureRegistry::class)->items()->pluck('key'))->toContain('tasks');
    expect(NotificationCategoryRegistry::keys())->toContain('task_assigned');

    app(ModuleRegistry::class)->setEnabled($household, 'tasks', false);

    // Poslije: nigdje, ali podaci su i dalje u bazi.
    expect(app(SearchService::class)->search('nestaje', $household)->pluck('type'))->not->toContain('task');
    expect(app(DashboardWidgetRegistry::class)->activeTitlesFor($household))->toBeEmpty();
    expect(app(CalendarService::class)->eventsBetween(now()->subDay(), now()->addDay(), $household))->toBeEmpty();
    expect(app(QuickCaptureRegistry::class)->items()->pluck('key'))->not->toContain('tasks');
    expect(NotificationCategoryRegistry::keys())->not->toContain('task_assigned');
    expect(Task::where('title', 'Zadatak koji nestaje')->exists())->toBeTrue();
});

it('hides a disabled module from navigation and blocks its route', function () {
    [$household, $owner] = makeHousehold();
    test()->actingAs($owner->user);
    Filament::setTenant($household);

    expect(TaskResource::shouldRegisterNavigation())->toBeTrue();
    expect(TaskResource::canAccess())->toBeTrue();

    app(ModuleRegistry::class)->setEnabled($household, 'tasks', false);

    expect(TaskResource::shouldRegisterNavigation())->toBeFalse();
    expect(TaskResource::canAccess())->toBeFalse();
    // Drugi modul je netaknut.
    expect(BillResource::shouldRegisterNavigation())->toBeTrue();

    test()->get(TaskResource::getUrl('index', ['tenant' => $household]))->assertForbidden();
});

it('renders the dashboard with every module turned off', function () {
    [$household, $owner] = makeHousehold();
    test()->actingAs($owner->user);
    Filament::setTenant($household);

    $registry = app(ModuleRegistry::class);

    foreach ($registry->all()->keys() as $key) {
        $registry->setEnabled($household, $key, false);
    }

    expect($registry->enabled($household))->toBeEmpty();

    Livewire::test(Dashboard::class)
        ->assertOk()
        ->assertSee('Nema uključenih aplikacija');
});

it('lets the owner toggle modules from the household settings page', function () {
    [$household, $owner] = makeHousehold();
    test()->actingAs($owner->user);
    Filament::setTenant($household);

    Livewire::test(EditHouseholdProfile::class, ['tenant' => $household])
        ->assertFormSet(['module_finance' => true])
        ->fillForm(['module_finance' => false])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(app(ModuleRegistry::class)->isEnabled('finance', $household->fresh()))->toBeFalse();
    // Naziv domaćinstva nije oštećen prekidačima (nisu kolone na tabeli).
    expect($household->fresh()->name)->toBe('Test');
});

it('does not let a plain member toggle modules', function () {
    [$household] = makeHousehold();
    $member = User::factory()->create();
    $household->members()->create(['user_id' => $member->id, 'role' => 'member', 'joined_at' => now()]);

    test()->actingAs($member);
    Filament::setTenant($household);

    // Prekidači se vide, ali su onemogućeni i nema dugmeta za snimanje.
    Livewire::test(EditHouseholdProfile::class, ['tenant' => $household])
        ->assertOk()
        ->assertFormFieldIsDisabled('module_finance')
        ->assertFormFieldIsDisabled('name');
});
