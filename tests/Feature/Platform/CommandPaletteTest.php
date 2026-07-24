<?php

use App\Modules\Tasks\Enums\Priority;
use App\Modules\Tasks\Enums\TaskStatus;
use App\Modules\Tasks\Models\Task;
use App\Platform\Filament\CommandPalette;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('app'));
});

it('shows a hint until at least two characters are entered', function () {
    [$household, $owner] = makeHousehold();
    test()->actingAs($owner->user);
    Filament::setTenant($household);

    Livewire::test(CommandPalette::class)
        ->assertOk()
        ->assertSee(__('search.hint'))
        ->set('q', 'a')
        ->assertSee(__('search.hint'));
});

it('aggregates task results grouped by type via SearchService', function () {
    [$household, $owner] = makeHousehold();
    test()->actingAs($owner->user);
    Filament::setTenant($household);

    Task::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'title' => 'Rezervisati godišnji odmor',
        'priority' => Priority::Medium,
        'status' => TaskStatus::Todo,
    ]);

    Livewire::test(CommandPalette::class)
        ->set('q', 'godišnji')
        ->assertSee('Rezervisati godišnji odmor')
        ->assertSee('Zadaci');
});

it('renders gracefully with no search modules registered', function () {
    config()->set('homeos-apps', []);
    [$household, $owner] = makeHousehold();
    test()->actingAs($owner->user);
    Filament::setTenant($household);

    Livewire::test(CommandPalette::class)
        ->set('q', 'bilo šta')
        ->assertOk()
        ->assertSee('Nema rezultata');
});
