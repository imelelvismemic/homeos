<?php

use App\Models\User;
use Filament\Facades\Filament;
use Filament\Pages\Auth\Register;
use Livewire\Livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('app'));
});

it('allows a new user to register', function () {
    Livewire::test(Register::class)
        ->set('data.name', 'Elvis Memić')
        ->set('data.email', 'elvis@example.com')
        ->set('data.password', 'password123')
        ->set('data.passwordConfirmation', 'password123')
        ->call('register')
        ->assertHasNoFormErrors();

    $user = User::where('email', 'elvis@example.com')->first();

    expect($user)->not->toBeNull();
    expect($user->current_household_id)->toBeNull();
});
