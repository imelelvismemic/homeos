<?php

use App\Modules\Tasks\Models\Task;
use Filament\Facades\Filament;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('app'));
});

it('serves calendar events for the shown range so the view can refresh in place', function () {
    [$household, $owner] = makeHousehold();

    Task::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'title' => 'Zadatak u rasponu',
        'due_date' => '2026-08-14 10:00',
    ]);

    Task::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'title' => 'Zadatak van raspona',
        'due_date' => '2026-11-14 10:00',
    ]);

    $url = route('filament.app.calendar-events', [
        'h' => $household->getKey(),
        'start' => '2026-08-01',
        'end' => '2026-09-01',
    ]);

    $response = test()->actingAs($owner->user)->getJson($url)->assertOk();

    $titles = collect($response->json())->pluck('title');

    expect($titles)->toContain('Zadatak u rasponu');
    expect($titles)->not->toContain('Zadatak van raspona');
});

it('never serves calendar events of a household the user does not belong to', function () {
    [$householdA] = makeHousehold();
    [, $ownerB] = makeHousehold();

    $url = route('filament.app.calendar-events', ['h' => $householdA->getKey()]);

    test()->actingAs($ownerB->user)->getJson($url)->assertStatus(404);
});

it('never serves calendar events to an unauthenticated visitor', function () {
    [$household] = makeHousehold();

    $url = route('filament.app.calendar-events', ['h' => $household->getKey()]);

    test()->getJson($url)->assertStatus(403);
});
