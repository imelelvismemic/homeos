<?php

use App\Modules\Tasks\Models\Task;
use App\Platform\Filament\Livewire\NotificationBell;
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

it('opens the notifications in a side panel from the bell, without leaving the page', function () {
    [$household, $owner] = makeHousehold();
    test()->actingAs($owner->user);
    Filament::setTenant($household);

    $task = Task::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'title' => 'Obavijest iz panela',
    ]);

    $owner->notify(new SharedWithYou($task));

    // Zatvoreno zvonce ne smije vući listu — brojač je jedino što treba.
    Livewire::test(NotificationBell::class)
        ->assertSet('unreadCount', 1)
        ->assertDontSee('Obavijest iz panela')
        ->call('openPanel')
        ->assertSet('panelOpen', true)
        ->assertSee('Obavijest iz panela')
        // Potvrda ide iz panela: korisnik ostaje tamo gdje je bio, panel ostaje otvoren.
        ->call('markAllRead')
        ->assertSet('panelOpen', true)
        ->assertDispatched('homeos-notifications-read', count: 0)
        ->call('closePanel')
        ->assertSet('panelOpen', false);

    expect($owner->fresh()->unreadNotifications()->count())->toBe(0);
});

it('offers the full inbox page as well, for the narrow screens and the history', function () {
    [$household, $owner] = makeHousehold();
    test()->actingAs($owner->user);
    Filament::setTenant($household);

    // Panel i stranica dijele istu logiku (trait), pa link na punu stranicu mora
    // ostati dostupan — na uskim ekranima je to jedini put do sandučeta.
    Livewire::test(NotificationBell::class)
        ->call('openPanel')
        ->assertSee(NotificationsInbox::getUrl(tenant: $household), escape: false);
});
