<?php

use App\Platform\Filament\Pages\UserProfile;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('app'));
});

it('opens the profile page inside the panel (tenant context, no 500)', function () {
    [$household, $owner] = makeHousehold();
    test()->actingAs($owner->user);
    Filament::setTenant($household);

    Livewire::test(UserProfile::class)
        ->assertOk()
        ->assertFormSet([
            'name' => $owner->user->name,
            'email' => $owner->user->email,
        ]);

    // Puna stranica u panelu: Filamentov ugrađeni ->profile() je ovdje vraćao 500
    // jer se registruje izvan tenant rute (bez domaćinstva u kontekstu).
    test()->get(UserProfile::getUrl(tenant: $household))->assertOk();
});

it('changes the password only with the current one', function () {
    [$household, $owner] = makeHousehold();
    $owner->user->update(['password' => Hash::make('stara-lozinka')]);
    test()->actingAs($owner->user->fresh());
    Filament::setTenant($household);

    Livewire::test(UserProfile::class)
        ->fillForm([
            'name' => $owner->user->name,
            'email' => $owner->user->email,
            'password' => 'nova-lozinka-123',
            'password_confirmation' => 'nova-lozinka-123',
            'current_password' => 'pogrešna',
        ])
        ->call('save')
        ->assertHasFormErrors(['current_password']);

    Livewire::test(UserProfile::class)
        ->fillForm([
            'name' => $owner->user->name,
            'email' => $owner->user->email,
            'password' => 'nova-lozinka-123',
            'password_confirmation' => 'nova-lozinka-123',
            'current_password' => 'stara-lozinka',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(Hash::check('nova-lozinka-123', $owner->user->fresh()->password))->toBeTrue();
});

it('stores the avatar on the private disk and removes it when cleared', function () {
    Storage::fake('documents');

    [$household, $owner] = makeHousehold();
    test()->actingAs($owner->user);
    Filament::setTenant($household);

    $owner->user->update(['avatar_path' => 'avatars/slika.png']);
    Storage::disk('documents')->put('avatars/slika.png', 'fake');

    expect($owner->user->fresh()->getFilamentAvatarUrl())->toContain('/profile/avatar/');

    Livewire::test(UserProfile::class)
        ->fillForm([
            'name' => $owner->user->name,
            'email' => $owner->user->email,
            'avatar_path' => null,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($owner->user->fresh()->avatar_path)->toBeNull();
    expect($owner->user->fresh()->getFilamentAvatarUrl())->toBeNull();
});

it('never serves an avatar to someone outside the household', function () {
    Storage::fake('documents');

    [$household, $owner] = makeHousehold();
    [, $stranger] = makeHousehold();

    $owner->user->update(['avatar_path' => 'avatars/slika.png']);
    Storage::disk('documents')->put('avatars/slika.png', 'fake');

    $url = route('filament.app.avatar', ['user' => $owner->user_id]);

    test()->actingAs($owner->user)->get($url)->assertOk();
    test()->actingAs($stranger->user)->get($url)->assertStatus(403);
});
