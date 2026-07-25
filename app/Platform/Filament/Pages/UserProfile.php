<?php

namespace App\Platform\Filament\Pages;

use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * Profil prijavljenog korisnika: ime, e-mail, profilna slika i promjena lozinke.
 *
 * NIJE Filamentova ugrađena `->profile()` stranica: ona se registruje IZVAN
 * tenant rute (bez `Filament::getTenant()`), pa je puni panel layout pucao s 500
 * kad navigacija zatraži URL-ove vezane za domaćinstvo. Ovo je obična stranica
 * panela — tenant je postavljen, sidebar/topbar rade normalno.
 *
 * Lozinka se mijenja samo uz potvrdu TRENUTNE lozinke (`current_password`), da
 * zatečena otvorena sesija ne znači i preuzimanje naloga.
 */
class UserProfile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.platform.pages.user-profile';

    /** @var array<string, mixed> */
    public array $data = [];

    public function getTitle(): string
    {
        return __('platform.profile.title');
    }

    public function mount(): void
    {
        $user = $this->user();

        $this->form->fill([
            'name' => $user->name,
            'email' => $user->email,
            'avatar_path' => $user->avatar_path,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('platform.profile.account_section'))
                    ->schema([
                        FileUpload::make('avatar_path')
                            ->label(__('platform.profile.avatar'))
                            ->helperText(__('platform.profile.avatar_help'))
                            ->avatar()
                            ->image()
                            ->imageEditor()
                            ->disk('documents')
                            ->directory('avatars')
                            ->visibility('private')
                            ->maxSize(4096)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']),

                        TextInput::make('name')
                            ->label(__('platform.profile.name'))
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label(__('platform.profile.email'))
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(table: 'users', column: 'email', ignorable: fn () => $this->user()),
                    ]),

                Section::make(__('platform.profile.password_section'))
                    ->description(__('platform.profile.password_help'))
                    ->schema([
                        TextInput::make('password')
                            ->label(__('platform.profile.new_password'))
                            ->password()
                            ->revealable()
                            ->autocomplete('new-password')
                            ->rule(Password::default())
                            ->live(debounce: 500)
                            ->same('password_confirmation'),

                        TextInput::make('password_confirmation')
                            ->label(__('platform.profile.new_password_confirmation'))
                            ->password()
                            ->revealable()
                            ->required()
                            ->visible(fn (Get $get): bool => filled($get('password'))),

                        TextInput::make('current_password')
                            ->label(__('platform.profile.current_password'))
                            ->helperText(__('platform.profile.current_password_help'))
                            ->password()
                            ->revealable()
                            ->autocomplete('current-password')
                            ->required()
                            ->rule('current_password')
                            ->visible(fn (Get $get): bool => filled($get('password'))),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $user = $this->user();

        $attributes = [
            'name' => $data['name'],
            'email' => $data['email'],
            // Prazno polje znači "ukloni sliku" — Filament tada vrati null/[].
            'avatar_path' => blank($data['avatar_path'] ?? null) ? null : $data['avatar_path'],
        ];

        if (filled($data['password'] ?? null)) {
            $attributes['password'] = Hash::make($data['password']);
        }

        $user->update($attributes);

        // Promjena lozinke inače izbacuje korisnika iz sesije (AuthenticateSession).
        if (isset($attributes['password']) && request()->hasSession()) {
            request()->session()->put('password_hash_'.filament()->getAuthGuard(), $user->getAuthPassword());
        }

        $this->form->fill([
            'name' => $user->name,
            'email' => $user->email,
            'avatar_path' => $user->avatar_path,
        ]);

        Notification::make()->title(__('platform.profile.saved'))->success()->send();
    }

    private function user(): User
    {
        /** @var User $user */
        $user = auth()->user();

        return $user;
    }
}
