<?php

use App\Models\User;
use App\Platform\Filament\Pages\RegisterUser;
use App\Platform\Filament\Tenancy\EditHouseholdProfile;
use App\Platform\Http\HouseholdInvitationController;
use App\Platform\Models\HouseholdInvitation;
use App\Platform\Notifications\HouseholdInvited;
use App\Platform\Services\HouseholdInvitationService;
use Filament\Facades\Filament;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('app'));
});

it('adds an already registered user immediately, without an invitation', function () {
    [$household, $owner] = makeHousehold();
    $existing = User::factory()->create(['email' => 'clan@example.com']);
    Notification::fake();

    $addedImmediately = app(HouseholdInvitationService::class)
        ->invite($household, 'clan@example.com', 'member', $owner->user);

    expect($addedImmediately)->toBeTrue();
    expect($household->members()->where('user_id', $existing->id)->exists())->toBeTrue();
    expect(HouseholdInvitation::count())->toBe(0);
    Notification::assertNothingSent();
});

it('emails an invitation link to someone who has no account yet', function () {
    [$household, $owner] = makeHousehold();
    Notification::fake();

    $addedImmediately = app(HouseholdInvitationService::class)
        ->invite($household, 'novi@example.com', 'member', $owner->user);

    expect($addedImmediately)->toBeFalse();

    $invitation = HouseholdInvitation::firstWhere('email', 'novi@example.com');

    expect($invitation)->not->toBeNull();
    expect($invitation->isPending())->toBeTrue();
    // U bazi stoji HASH — sam token postoji samo u linku iz emaila.
    expect(strlen($invitation->token))->toBe(64);

    Notification::assertSentOnDemand(HouseholdInvited::class);
});

it('lets an invited person register and land straight in the household', function () {
    [$household, $owner] = makeHousehold();
    $token = null;

    Notification::fake();
    app(HouseholdInvitationService::class)->invite($household, 'novi@example.com', 'member', $owner->user);
    Notification::assertSentOnDemand(HouseholdInvited::class, function (HouseholdInvited $n) use (&$token) {
        $token = $n->token;

        return true;
    });

    // 1. Klik na link iz emaila → registracija, token čeka u sesiji.
    test()->get(route('household-invitation', ['token' => $token]))
        ->assertRedirect(Filament::getRegistrationUrl());

    expect(session(HouseholdInvitationController::SESSION_KEY))->toBe($token);

    // 2. Email je popunjen iz pozivnice i zaključan.
    Livewire::test(RegisterUser::class)
        ->assertFormSet(['email' => 'novi@example.com'])
        ->fillForm([
            'name' => 'Novi Član',
            'email' => 'novi@example.com',
            'password' => 'tajna-lozinka-1',
            'passwordConfirmation' => 'tajna-lozinka-1',
        ])
        ->call('register');

    // 3. Nalog je kreiran i ODMAH je član — bez kreiranja vlastitog domaćinstva.
    $user = User::firstWhere('email', 'novi@example.com');

    expect($user)->not->toBeNull();
    expect($household->fresh()->members()->where('user_id', $user->id)->exists())->toBeTrue();
    expect($user->current_household_id)->toBe($household->id);
    expect(HouseholdInvitation::firstWhere('email', 'novi@example.com')->accepted_at)->not->toBeNull();
});

it('accepts the invitation on login when the person already has an account', function () {
    [$household, $owner] = makeHousehold();
    $user = User::factory()->create(['email' => 'poznat@example.com']);
    $token = 'token-za-test';

    HouseholdInvitation::create([
        'household_id' => $household->id,
        'invited_by' => $owner->user_id,
        'email' => 'poznat@example.com',
        'role' => 'member',
        'token' => hash('sha256', $token),
        'expires_at' => now()->addDays(7),
    ]);

    // Link vodi na prijavu i ostavlja token u sesiji.
    test()->get(route('household-invitation', ['token' => $token]))
        ->assertRedirect(Filament::getLoginUrl());

    // Prijava (Login event) prihvata pozivnicu.
    test()->actingAs($user);
    event(new Login('web', $user, false));

    expect($household->members()->where('user_id', $user->id)->exists())->toBeTrue();
});

it('refuses a used, expired or unknown token', function () {
    [$household, $owner] = makeHousehold();
    $service = app(HouseholdInvitationService::class);

    expect($service->findPending('nepostojeci'))->toBeNull();

    $expired = HouseholdInvitation::create([
        'household_id' => $household->id,
        'invited_by' => $owner->user_id,
        'email' => 'istekla@example.com',
        'role' => 'member',
        'token' => hash('sha256', 'istekla'),
        'expires_at' => now()->subDay(),
    ]);

    expect($service->findPending('istekla'))->toBeNull();
    expect($expired->isPending())->toBeFalse();

    $used = HouseholdInvitation::create([
        'household_id' => $household->id,
        'invited_by' => $owner->user_id,
        'email' => 'iskoristena@example.com',
        'role' => 'member',
        'token' => hash('sha256', 'iskoristena'),
        'expires_at' => now()->addDay(),
        'accepted_at' => now(),
    ]);

    expect($service->findPending('iskoristena'))->toBeNull();
    expect($used->isPending())->toBeFalse();
});

it('never lets a forwarded link put the wrong account into the household', function () {
    [$household, $owner] = makeHousehold();
    $stranger = User::factory()->create(['email' => 'neko.drugi@example.com']);

    $invitation = HouseholdInvitation::create([
        'household_id' => $household->id,
        'invited_by' => $owner->user_id,
        'email' => 'pozvani@example.com',
        'role' => 'member',
        'token' => hash('sha256', 'proslijedjen'),
        'expires_at' => now()->addDay(),
    ]);

    expect(fn () => app(HouseholdInvitationService::class)->accept($invitation, $stranger))
        ->toThrow(RuntimeException::class);

    expect($household->members()->where('user_id', $stranger->id)->exists())->toBeFalse();
});

it('shows pending invitations to the owner and lets them be revoked', function () {
    [$household, $owner] = makeHousehold();
    test()->actingAs($owner->user);
    Filament::setTenant($household);
    Notification::fake();

    app(HouseholdInvitationService::class)->invite($household, 'novi@example.com', 'member', $owner->user);

    $invitation = HouseholdInvitation::firstWhere('email', 'novi@example.com');

    Livewire::test(EditHouseholdProfile::class, ['tenant' => $household])
        ->assertSee('novi@example.com')
        ->call('revokeInvitation', $invitation->getKey());

    expect(HouseholdInvitation::count())->toBe(0);
});

it('does not let a plain member see or revoke invitations', function () {
    [$household, $owner] = makeHousehold();
    Notification::fake();
    app(HouseholdInvitationService::class)->invite($household, 'novi@example.com', 'member', $owner->user);

    $member = User::factory()->create();
    $household->members()->create(['user_id' => $member->id, 'role' => 'member', 'joined_at' => now()]);

    test()->actingAs($member);
    Filament::setTenant($household);

    $page = Livewire::test(EditHouseholdProfile::class, ['tenant' => $household])
        ->assertDontSee('novi@example.com');

    $page->call('revokeInvitation', HouseholdInvitation::first()->getKey());

    expect(HouseholdInvitation::count())->toBe(1);
});
