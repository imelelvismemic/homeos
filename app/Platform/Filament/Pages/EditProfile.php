<?php

namespace App\Platform\Filament\Pages;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;

/**
 * Profil korisnika (ime, e-mail, promjena lozinke). Filament nudi ovu stranicu
 * ugrađeno, ali bez potvrde trenutne lozinke — a promjena lozinke bez nje znači
 * da svako ko zatekne otvorenu sesiju može preuzeti nalog. Zato dodajemo polje
 * "Trenutna lozinka" (validacija Laravel `current_password`), koje se pojavi tek
 * kad korisnik krene upisivati novu.
 *
 * Nije Filament navigaciona stranica (nasljeđuje $isDiscovered = false) — otvara
 * se iz korisničkog menija, registrovana kroz ->profile() u HomePanelProvideru.
 */
class EditProfile extends BaseEditProfile
{
    public function getTitle(): string
    {
        return __('platform.profile.title');
    }

    public function getHeading(): string
    {
        return __('platform.profile.title');
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            $this->getNameFormComponent(),
            $this->getEmailFormComponent(),
            $this->getPasswordFormComponent(),
            $this->getPasswordConfirmationFormComponent(),
            $this->getCurrentPasswordFormComponent(),
        ]);
    }

    protected function getCurrentPasswordFormComponent(): Component
    {
        return TextInput::make('currentPassword')
            ->label(__('platform.profile.current_password'))
            ->helperText(__('platform.profile.current_password_help'))
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->autocomplete('current-password')
            ->required()
            ->rule('current_password')
            // Traži se samo kad se lozinka stvarno mijenja (isto kao potvrda).
            ->visible(fn (Get $get): bool => filled($get('password')))
            ->dehydrated(false);
    }
}
