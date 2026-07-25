<?php

namespace App\Platform\Filament\Pages;

use App\Models\User;
use App\Platform\Enums\DigestFrequency;
use App\Platform\Models\HouseholdMember;
use App\Platform\Models\NotificationPreference;
use App\Platform\Notifications\NotificationCategoryRegistry;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * Profil prijavljenog korisnika, na jednom mjestu: podaci naloga i profilna
 * slika, promjena lozinke, i postavke obavještenja (email po kategoriji + ritam
 * sažetka) — sve kroz kartice iste forme, umjesto zasebne stavke u meniju.
 *
 * NIJE Filamentova ugrađena `->profile()` stranica: ona se registruje IZVAN
 * tenant rute (bez `Filament::getTenant()`), pa je puni panel layout pucao s 500
 * kad navigacija zatraži URL-ove vezane za domaćinstvo. Ovo je obična stranica
 * panela — tenant je postavljen, sidebar/topbar rade normalno.
 *
 * Lozinka se mijenja samo uz potvrdu TRENUTNE lozinke (`current_password`), da
 * zatečena otvorena sesija ne znači i preuzimanje naloga. Postavke obavještenja
 * su vezane za ČLANA (domaćinstvo u kojem je korisnik trenutno), ostatak za
 * korisnika — zato se snimaju u istoj radnji, ali na dva zapisa.
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
        $this->form->fill($this->currentState());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('profile')
                    ->tabs([
                        Tabs\Tab::make(__('platform.profile.account_section'))
                            ->icon('heroicon-m-user')
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
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    // Privatni lokalni disk nema javni URL (ni temporaryUrl),
                                    // pa bi pregled snimljene slike bio prazan kvadrat —
                                    // vodimo ga na našu autentikovanu rutu.
                                    ->getUploadedFileUsing(fn (?string $file): ?array => $this->uploadedAvatar($file)),

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

                        Tabs\Tab::make(__('platform.profile.password_section'))
                            ->icon('heroicon-m-key')
                            ->schema([
                                TextInput::make('password')
                                    ->label(__('platform.profile.new_password'))
                                    ->helperText(__('platform.profile.password_help'))
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

                        Tabs\Tab::make(__('platform.settings.title'))
                            ->icon('heroicon-m-bell-alert')
                            ->schema([
                                Section::make(__('platform.settings.email_section'))
                                    ->description(__('platform.settings.subheading'))
                                    ->schema($this->notificationToggles()),

                                Section::make(__('platform.settings.digest_section'))
                                    ->description(__('platform.settings.digest_help'))
                                    ->schema([
                                        Select::make('digest_frequency')
                                            ->label(__('platform.settings.digest_frequency'))
                                            ->options(DigestFrequency::options())
                                            ->required(),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
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

        $this->saveNotificationPreferences($data);

        $this->form->fill($this->currentState());

        Notification::make()->title(__('platform.profile.saved'))->success()->send();
    }

    /**
     * Prekidači za email po kategoriji — kategorije dolaze iz registryja (svaki
     * modul prijavi svoje), pa profil ne zna za module (CLAUDE.md §10/§12).
     *
     * @return array<int, Toggle>
     */
    private function notificationToggles(): array
    {
        return collect(NotificationCategoryRegistry::labelled())
            ->map(fn (string $label, string $category) => Toggle::make("email_{$category}")
                ->label($label))
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function saveNotificationPreferences(array $data): void
    {
        $member = $this->currentMember();

        if ($member === null) {
            return;
        }

        $member->update(['digest_frequency' => $data['digest_frequency'] ?? DigestFrequency::None->value]);

        foreach (NotificationCategoryRegistry::keys() as $category) {
            NotificationPreference::updateOrCreate(
                ['household_member_id' => $member->id, 'category' => $category],
                ['email_enabled' => (bool) ($data["email_{$category}"] ?? true)],
            );
        }
    }

    /**
     * Trenutno stanje forme (korisnik + postavke člana).
     *
     * @return array<string, mixed>
     */
    private function currentState(): array
    {
        $user = $this->user();
        $member = $this->currentMember();

        $preferences = $member?->notificationPreferences()->pluck('email_enabled', 'category') ?? collect();

        $state = [
            'name' => $user->name,
            'email' => $user->email,
            'avatar_path' => $user->avatar_path,
            'digest_frequency' => $member?->digest_frequency?->value ?? DigestFrequency::None->value,
        ];

        foreach (NotificationCategoryRegistry::keys() as $category) {
            $state["email_{$category}"] = (bool) ($preferences[$category] ?? true);
        }

        return $state;
    }

    /**
     * Podaci o snimljenoj slici za pregled u formi (naziv, veličina, tip, URL).
     *
     * @return array<string, mixed>|null
     */
    private function uploadedAvatar(?string $file): ?array
    {
        if (blank($file)) {
            return null;
        }

        return [
            'name' => basename($file),
            'size' => 0,
            'type' => null,
            'url' => route('filament.app.avatar', [
                'user' => $this->user()->getKey(),
                'v' => $this->user()->updated_at?->timestamp,
            ]),
        ];
    }

    private function currentMember(): ?HouseholdMember
    {
        $household = Filament::getTenant();

        return $household?->members()->where('user_id', auth()->id())->first();
    }

    private function user(): User
    {
        /** @var User $user */
        $user = auth()->user();

        return $user;
    }
}
