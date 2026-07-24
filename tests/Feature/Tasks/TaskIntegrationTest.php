<?php

use App\Modules\Tasks\Calendar\TaskCalendarSource;
use App\Modules\Tasks\Dashboard\TaskDashboardWidget;
use App\Modules\Tasks\Enums\Priority;
use App\Modules\Tasks\Enums\TaskStatus;
use App\Modules\Tasks\Filament\Pages\KanbanBoard;
use App\Modules\Tasks\Filament\Widgets\TodayTasksWidget;
use App\Modules\Tasks\Models\Task;
use App\Modules\Tasks\Search\TaskSearchProvider;
use App\Platform\Calendar\CalendarService;
use App\Platform\Dashboard\DashboardWidgetRegistry;
use App\Platform\Search\SearchService;
use Filament\Facades\Filament;
use Livewire\Livewire;

/**
 * Definicija završenosti Faze 3: jedan zadatak s rokom pojavi se AUTOMATSKI na
 * dashboardu, kalendaru i kanbanu — bez ručnog povezivanja u tim modulima.
 * Sve tri tačke čitaju isti Task preko platforme (registry / CalendarSourceContract).
 */
beforeEach(function () {
    // Samo Zadaci registrovani — dokazuje da core radi i s jednim modulom.
    config()->set('homeos-apps', [
        'tasks' => [
            'name' => 'Zadaci',
            'enabled' => true,
            'dashboard_widget' => TaskDashboardWidget::class,
            'search_provider' => TaskSearchProvider::class,
            'calendar_source' => TaskCalendarSource::class,
        ],
    ]);

    Filament::setCurrentPanel(Filament::getPanel('app'));
});

function actAsOwnerWith(): array
{
    [$household, $owner] = makeHousehold();
    test()->actingAs($owner->user);
    Filament::setTenant($household);

    return [$household, $owner];
}

it('shows a due task on the Today dashboard widget', function () {
    [$household, $owner] = actAsOwnerWith();

    Task::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'title' => 'Platiti račun',
        'priority' => Priority::High,
        'status' => TaskStatus::Todo,
        'due_date' => today(),
    ]);

    expect(app(TaskDashboardWidget::class)->hasContentFor($household))->toBeTrue();

    // Core registry (bez znanja o Tasks) vraća ovaj widget kao aktivan.
    $widgetClasses = app(DashboardWidgetRegistry::class)->widgetClassesFor($household);
    expect($widgetClasses)->toHaveCount(1);
    expect($widgetClasses[0])->toBe(TodayTasksWidget::class);
});

it('exposes a due task to the aggregated calendar without Calendar knowing about Tasks', function () {
    [$household, $owner] = actAsOwnerWith();

    $task = Task::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'title' => 'Ljekar u 15h',
        'priority' => Priority::Medium,
        'status' => TaskStatus::Todo,
        'due_date' => now()->addDays(3),
    ]);

    $events = app(CalendarService::class)->eventsBetween(
        now()->subWeek(),
        now()->addWeeks(2),
        $household,
    );

    expect($events)->toHaveCount(1);
    expect($events->first()->title)->toBe('Ljekar u 15h');
    expect($events->first()->id)->toBe($task->id);
});

it('shows a task in its status column on the kanban board', function () {
    [$household, $owner] = actAsOwnerWith();

    Task::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'title' => 'Pokositi travu',
        'priority' => Priority::Low,
        'status' => TaskStatus::InProgress,
        'due_date' => now()->addDay(),
    ]);

    Livewire::test(KanbanBoard::class)
        ->assertOk()
        ->assertSee('Pokositi travu');
});

it('moving a kanban card updates the task status', function () {
    [$household, $owner] = actAsOwnerWith();

    $task = Task::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'title' => 'Usisati',
        'priority' => Priority::Medium,
        'status' => TaskStatus::Todo,
    ]);

    Livewire::test(KanbanBoard::class)
        ->call('moveTask', $task->id, TaskStatus::Done->value);

    expect($task->fresh()->status)->toBe(TaskStatus::Done);
    expect($task->fresh()->completed_at)->not->toBeNull();
});

it('finds a task through the aggregated search', function () {
    [$household, $owner] = actAsOwnerWith();

    Task::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'title' => 'Rezervisati godišnji odmor',
        'priority' => Priority::Medium,
        'status' => TaskStatus::Todo,
    ]);

    $results = app(SearchService::class)->search('godišnji', $household);

    expect($results)->toHaveCount(1);
    expect($results->first()->type)->toBe('task');
    expect($results->first()->title)->toContain('godišnji');
});

it('universal search matches task text (title/description) but not assignee or tags', function () {
    // Pretraga po odgovornoj osobi i oznakama je namjerno SAMO u search boxu
    // liste zadataka (TaskResource tabela), ne u univerzalnoj pretrazi.
    [$household, $owner, $members] = makeHousehold(extraMembers: 1);
    test()->actingAs($owner->user);
    Filament::setTenant($household);
    $assignee = $members[0];

    $task = Task::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'title' => 'Neki zadatak',
        'description' => 'sa opisom koji sadrži ključnu riječ paprika',
        'priority' => Priority::Medium,
        'status' => TaskStatus::Todo,
        'assigned_to' => $assignee->id,
    ]);
    $task->tag(['vikend']);

    // Po naslovu i opisu — da.
    expect(app(SearchService::class)->search('Neki', $household))->toHaveCount(1);
    expect(app(SearchService::class)->search('paprika', $household))->toHaveCount(1);

    // Po oznaci i imenu odgovorne osobe — ne (to je u pretrazi liste).
    expect(app(SearchService::class)->search('vikend', $household))->toBeEmpty();
    expect(app(SearchService::class)->search($assignee->user->name, $household))->toBeEmpty();
});
