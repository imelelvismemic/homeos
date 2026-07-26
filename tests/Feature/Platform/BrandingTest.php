<?php

use App\Modules\Tasks\Models\Task;
use App\Platform\Filament\Livewire\NotificationBell;
use App\Platform\Filament\Pages\Dashboard;
use App\Platform\Notifications\SharedWithYou;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('app'));
});

it('shows the product name and version from a single source', function () {
    // Naziv i verzija su u kodu (config/homeos.php), ne u .env — inače bi se
    // morali ručno mijenjati na serveru i razišli bi se (Faza 8/9).
    expect(config('homeos.name'))->toBe('HomeOS plus');

    test()->getJson('/health')
        ->assertOk()
        ->assertJson(['version' => config('homeos.version')]);
});

it('renders the brand mark and the footer on a panel page', function () {
    [$household, $owner] = makeHousehold();
    test()->actingAs($owner->user);
    Filament::setTenant($household);

    $page = test()->get(Dashboard::getUrl(tenant: $household))->assertOk();

    $page->assertSee('HomeOS', escape: false);                    // wordmark u meniju
    $page->assertSee('plus', escape: false);                      // drugi dio, u boji teme
    // Potpis je doslovan: ©elvismemic v<verzija>
    $page->assertSee('©elvismemic v'.config('homeos.version'), escape: false);
});

it('counts unread notifications in the bell and refreshes itself', function () {
    [$household, $owner] = makeHousehold();
    test()->actingAs($owner->user);
    Filament::setTenant($household);

    $bell = Livewire::test(NotificationBell::class)
        ->assertOk()
        ->assertSet('unreadCount', 0);

    $task = Task::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'title' => 'Nova obavijest',
    ]);

    $owner->notify(new SharedWithYou($task));

    // Ranije bi brojač ostao na nuli do sljedećeg učitavanja stranice; sada ga
    // osvježi poll (wire:poll → refreshCount).
    $bell->call('refreshCount')->assertSet('unreadCount', 1);
});

it('drops the bell counter the moment the inbox marks everything read', function () {
    [$household, $owner] = makeHousehold();
    test()->actingAs($owner->user);
    Filament::setTenant($household);

    $task = Task::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'title' => 'Za čitanje',
    ]);
    $owner->notify(new SharedWithYou($task));

    $bell = Livewire::test(NotificationBell::class)->assertSet('unreadCount', 1);

    $owner->unreadNotifications->markAsRead();

    // Sanduče emituje ovaj event nakon "označi sve pročitanim" — zvonce ne čeka poll.
    $bell->dispatch('homeos-notifications-read')->assertSet('unreadCount', 0);
});
