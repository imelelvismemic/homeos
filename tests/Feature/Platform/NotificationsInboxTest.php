<?php

use App\Modules\Tasks\Models\Task;
use App\Platform\Filament\Pages\NotificationsInbox;
use App\Platform\Notifications\SharedWithYou;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('app'));
});

it('lists the current member in-app notifications and marks them read', function () {
    [$household, $owner] = makeHousehold();
    test()->actingAs($owner->user);
    Filament::setTenant($household);

    $task = Task::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'title' => 'Podijeljeni zadatak',
    ]);

    $owner->notify(new SharedWithYou($task));

    expect($owner->unreadNotifications()->count())->toBe(1);

    Livewire::test(NotificationsInbox::class)
        ->assertOk()
        ->assertSee('Podijeljeni zadatak')
        ->call('markAllRead')
        // Zvonce u topbaru je server-renderovano, pa mu sanduče javi novi broj.
        ->assertDispatched('homeos-notifications-read', count: 0);

    expect($owner->fresh()->unreadNotifications()->count())->toBe(0);
});

it('hides read notifications by default and shows them on request', function () {
    [$household, $owner] = makeHousehold();
    test()->actingAs($owner->user);
    Filament::setTenant($household);

    $task = Task::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'title' => 'Stara obavijest',
    ]);

    $owner->notify(new SharedWithYou($task));
    $owner->unreadNotifications->markAsRead();

    Livewire::test(NotificationsInbox::class)
        ->assertDontSee('Stara obavijest')
        ->call('toggleShowRead')
        ->assertSee('Stara obavijest');
});
