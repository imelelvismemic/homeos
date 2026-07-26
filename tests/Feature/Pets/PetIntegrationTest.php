<?php

use App\Modules\Pets\Dashboard\PetsDashboardWidget;
use App\Modules\Pets\Enums\CareType;
use App\Modules\Pets\Enums\PetSpecies;
use App\Modules\Pets\Filament\Resources\PetResource;
use App\Modules\Pets\Models\CareRecord;
use App\Modules\Pets\Models\Pet;
use App\Platform\Calendar\CalendarService;
use App\Platform\Dashboard\DashboardWidgetRegistry;
use App\Platform\Digest\DigestService;
use App\Platform\Models\Household;
use App\Platform\Models\HouseholdMember;
use App\Platform\Modules\ModuleRegistry;
use App\Platform\QuickCapture\QuickCaptureRegistry;
use App\Platform\Search\SearchService;
use Filament\Facades\Filament;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('app'));
});

/** @return array{0: Household, 1: HouseholdMember, 2: Pet} */
function petContext(): array
{
    [$household, $owner] = makeHousehold();
    test()->actingAs($owner->user);
    Filament::setTenant($household);

    $pet = Pet::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'name' => 'Luna',
        'species' => PetSpecies::Dog,
        'notes' => 'Alergična na piletinu',
    ]);

    return [$household, $owner, $pet];
}

it('shows scheduled care on the Today dashboard without the dashboard knowing about Pets', function () {
    [$household, $owner, $pet] = petContext();

    CareRecord::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'pet_id' => $pet->id,
        'type' => CareType::VetVisit,
        'due_date' => now()->addDays(2),
    ]);

    expect(app(PetsDashboardWidget::class)->hasContentFor($household))->toBeTrue();
    expect(app(DashboardWidgetRegistry::class)->activeTitlesFor($household))
        ->toContain(__('pets.widget.heading'));
});

it('exposes scheduled care to the aggregated calendar', function () {
    [$household, $owner, $pet] = petContext();

    CareRecord::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'pet_id' => $pet->id,
        'type' => CareType::Vaccination,
        'due_date' => now()->addDays(3),
    ]);

    $events = app(CalendarService::class)->eventsBetween(now()->subWeek(), now()->addWeek(), $household);

    expect($events->contains(fn ($e) => $e->type === 'pet_care' && str_contains($e->title, 'Luna')))->toBeTrue();
});

it('finds a pet through the aggregated search', function () {
    [$household] = petContext();

    $results = app(SearchService::class)->search('Luna', $household);

    expect($results->contains(fn ($r) => $r->type === 'pet' && $r->title === 'Luna'))->toBeTrue();
});

it('offers the pet in quick capture and creates it through the generic endpoint', function () {
    [$household, $owner] = petContext();

    expect(app(QuickCaptureRegistry::class)->items()->pluck('key'))->toContain('pets');

    test()->actingAs($owner->user)
        ->postJson(route('filament.app.quick-create', ['key' => 'pets', 'h' => $household->getKey()]), [
            'name' => 'Mrvica',
        ])
        ->assertOk();

    expect(Pet::where('name', 'Mrvica')->where('household_id', $household->id)->exists())->toBeTrue();
});

it('contributes upcoming care to the digest', function () {
    [$household, $owner, $pet] = petContext();

    CareRecord::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'pet_id' => $pet->id,
        'type' => CareType::Treatment,
        'due_date' => now()->addDay(),
    ]);

    $sections = app(DigestService::class)->sectionsFor($household, $owner->user, now(), now()->addDays(7));
    $titles = collect($sections)->map(fn ($s) => $s->title);

    expect($titles)->toContain(__('pets.plural_label'));
});

it('disappears everywhere when the household turns the module off', function () {
    [$household, $owner, $pet] = petContext();

    CareRecord::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'pet_id' => $pet->id,
        'type' => CareType::Vaccination,
        'due_date' => now()->addDays(2),
    ]);

    app(ModuleRegistry::class)->setEnabled($household, 'pets', false);

    expect(app(SearchService::class)->search('Luna', $household)->pluck('type'))->not->toContain('pet');
    expect(app(CalendarService::class)->eventsBetween(now()->subWeek(), now()->addWeek(), $household)
        ->contains(fn ($e) => $e->type === 'pet_care'))->toBeFalse();
    expect(app(QuickCaptureRegistry::class)->items()->pluck('key'))->not->toContain('pets');
    expect(PetResource::shouldRegisterNavigation())->toBeFalse();
    expect(Pet::whereKey($pet->id)->exists())->toBeTrue();
});

it('works when every other module is switched off', function () {
    // Graceful degradation u oba smjera: novi modul ne smije zavisiti od ostalih.
    config()->set('homeos-apps', ['pets' => config('homeos-apps.pets')]);

    [$household, $owner, $pet] = petContext();

    CareRecord::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'pet_id' => $pet->id,
        'type' => CareType::Vaccination,
        'due_date' => now()->addDays(2),
    ]);

    expect(app(SearchService::class)->search('Luna', $household)->pluck('type'))->toContain('pet');
    expect(app(PetsDashboardWidget::class)->hasContentFor($household))->toBeTrue();
});
