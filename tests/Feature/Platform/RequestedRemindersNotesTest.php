<?php

use App\Modules\Notes\Models\Note;
use App\Modules\Reminders\Dashboard\ReminderDashboardWidget;
use App\Modules\Reminders\Models\Reminder;
use App\Modules\Tasks\Enums\Priority;
use App\Modules\Tasks\Enums\TaskStatus;
use App\Modules\Tasks\Models\Task;
use App\Platform\Calendar\CalendarService;
use App\Platform\Events\NoteRequested;
use App\Platform\Events\ReminderRequested;
use App\Platform\Search\SearchService;
use Filament\Facades\Filament;

/**
 * DoD Faze 4: podsjetnik/bilješka se kreiraju vezani za postojeći entitet KROZ
 * javni interfejs (platform event), bez cross-module importa ni direktnog pristupa
 * bazi. Plus integracija: podsjetnik s vremenom se automatski pojavi na
 * kalendaru/dashboardu/pretrazi.
 */
beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('app'));
});

function makeTaskFor($household, $owner, string $title = 'Zadatak'): Task
{
    return Task::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'title' => $title,
        'priority' => Priority::Medium,
        'status' => TaskStatus::Todo,
    ]);
}

it('creates a reminder tied to a task via ReminderRequested (no cross-module import)', function () {
    [$household, $owner] = makeHousehold();
    test()->actingAs($owner->user);
    $task = makeTaskFor($household, $owner, 'Platiti struju');

    ReminderRequested::dispatch($task, now()->addDay(), 'Podsjetnik: Platiti struju');

    $reminder = Reminder::firstWhere('title', 'Podsjetnik: Platiti struju');

    expect($reminder)->not->toBeNull();
    expect($reminder->household_id)->toBe($household->id);
    expect($reminder->remindable_id)->toBe($task->id);
    expect($reminder->remindable->is($task))->toBeTrue();
});

it('carries the assignee through ReminderRequested (reminder inherits responsible member)', function () {
    [$household, $owner, $members] = makeHousehold(extraMembers: 1);
    test()->actingAs($owner->user);
    $assignee = $members[0];
    $task = makeTaskFor($household, $owner, 'Odnijeti paket');

    ReminderRequested::dispatch($task, now()->addDay(), 'Podsjetnik: Odnijeti paket', assignedTo: $assignee->id);

    $reminder = Reminder::firstWhere('title', 'Podsjetnik: Odnijeti paket');

    expect($reminder->assigned_to)->toBe($assignee->id);
});

it('creates a note tied to a task via NoteRequested', function () {
    [$household, $owner] = makeHousehold();
    test()->actingAs($owner->user);
    $task = makeTaskFor($household, $owner, 'Kupiti farbu');

    NoteRequested::dispatch($task, '<p>Kupiti bijelu, mat.</p>', 'Bilješka uz zadatak: Kupiti farbu');

    $note = Note::firstWhere('title', 'Bilješka uz zadatak: Kupiti farbu');

    expect($note)->not->toBeNull();
    expect($note->household_id)->toBe($household->id);
    expect($note->notable->is($task))->toBeTrue();
});

it('shows a due reminder on the calendar, dashboard and search automatically', function () {
    [$household, $owner] = makeHousehold();
    test()->actingAs($owner->user);
    Filament::setTenant($household);

    Reminder::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'title' => 'Ljekarski pregled',
        'due_date' => today()->setHour(10),
    ]);

    // Kalendar (agregira preko CalendarSourceContract)
    $events = app(CalendarService::class)->eventsBetween(now()->subWeek(), now()->addWeek(), $household);
    expect($events->contains(fn ($e) => $e->type === 'reminder' && $e->title === 'Ljekarski pregled'))->toBeTrue();

    // Pretraga (agregira preko SearchProviderContract)
    $results = app(SearchService::class)->search('Ljekarski', $household);
    expect($results->contains(fn ($r) => $r->type === 'reminder'))->toBeTrue();

    // Dashboard widget
    expect(app(ReminderDashboardWidget::class)->hasContentFor($household))->toBeTrue();
});
