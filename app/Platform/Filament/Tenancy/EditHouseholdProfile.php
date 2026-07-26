<?php

namespace App\Platform\Filament\Tenancy;

use App\Models\User;
use App\Platform\Models\HouseholdInvitation;
use App\Platform\Models\HouseholdMember;
use App\Platform\Modules\ModuleRegistry;
use App\Platform\Services\HouseholdInvitationService;
use App\Platform\Services\HouseholdMemberService;
use App\Platform\Support\Currency;
use Filament\Actions\Action as PageAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Tenancy\EditTenantProfile;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use RuntimeException;

/**
 * Postavke domaćinstva: naziv + članovi, na jednom mjestu (nema zasebnih stavki
 * u meniju za jedno ni za drugo).
 *
 * Pristup: SVAKI član smije otvoriti stranicu i vidjeti ko je u domaćinstvu —
 * zato `canView()` ne koristi Filamentov default (Policy `update`, koji je
 * vlasnik-only). Izmjene su i dalje vlasnikove: polje naziva je onemogućeno,
 * dugme za snimanje se ne prikazuje, a radnje nad članovima (pozovi, promijeni
 * ulogu, prenesi vlasništvo, ukloni) su skrivene članovima.
 */
class EditHouseholdProfile extends EditTenantProfile implements HasTable
{
    use InteractsWithTable;

    protected static string $view = 'filament.platform.pages.household-profile';

    public static function getLabel(): string
    {
        return __('platform.household.settings_label');
    }

    /** Vidjeti postavke smije svaki član; mijenjati ih samo vlasnik. */
    public static function canView(Model $tenant): bool
    {
        return $tenant->members()->where('user_id', auth()->id())->exists();
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')
                ->label(__('platform.household.name'))
                ->placeholder(__('platform.household.name_placeholder'))
                ->helperText(fn (): ?string => static::currentUserIsOwner() ? null : __('platform.members.only_owner'))
                ->required()
                ->maxLength(255)
                ->disabled(fn (): bool => ! static::currentUserIsOwner()),

            Select::make('currency')
                ->label(__('platform.household.currency'))
                ->helperText(__('platform.household.currency_help'))
                ->options(Currency::options())
                ->searchable()
                ->required()
                ->disabled(fn (): bool => ! static::currentUserIsOwner())
                // Valuta se nudi samo ako je uključena aplikacija kojoj treba.
                // Pita se registry (`uses_currency`), ne konkretan modul po imenu —
                // buduća app s iznosima dobija isto ponašanje bez izmjene ovdje.
                ->visible(fn (): bool => $this->anyEnabledModuleUsesCurrency()),

            Section::make(__('platform.modules.section'))
                ->description(__('platform.modules.description'))
                ->schema($this->moduleToggles())
                ->columns(2),
        ]);
    }

    /**
     * Poslane pozivnice koje još nisu iskorištene — prikazuju se ispod liste
     * članova, da vlasnik vidi koga je pozvao i može povući poziv.
     *
     * @return Collection<int, HouseholdInvitation>
     */
    public function pendingInvitations(): Collection
    {
        if (! static::currentUserIsOwner()) {
            return collect();
        }

        return HouseholdInvitation::query()
            ->where('household_id', $this->tenant->getKey())
            ->pending()
            ->orderBy('email')
            ->get();
    }

    /** Povuci poslanu pozivnicu (samo vlasnik). */
    public function revokeInvitation(int $id): void
    {
        if (! static::currentUserIsOwner()) {
            return;
        }

        HouseholdInvitation::query()
            ->where('household_id', $this->tenant->getKey())
            ->whereKey($id)
            ->delete();

        Notification::make()->title(__('platform.invitations.revoked'))->success()->send();
    }

    /** Ima li ijedna uključena aplikacija iznose (registry ključ `uses_currency`). */
    private function anyEnabledModuleUsesCurrency(): bool
    {
        return app(ModuleRegistry::class)->enabled($this->tenant)
            ->contains(fn (array $app) => (bool) ($app['uses_currency'] ?? false));
    }

    /**
     * Prekidač po modulu — lista i nazivi dolaze iz registryja
     * (config/homeos-apps.php), pa nova app dobija svoj prekidač sama od sebe.
     *
     * Naziv ide kroz `ModuleRegistry::name()`, koji razrješava prijevodni ključ iz
     * configa (Faza 9c) — sam config nosi ključ, jer se u produkciji kešira.
     *
     * @return array<int, Toggle>
     */
    private function moduleToggles(): array
    {
        $registry = app(ModuleRegistry::class);

        return $registry->all()
            ->map(fn (array $app, string $key) => Toggle::make("module_{$key}")
                ->label(fn (): string => $registry->name($key))
                ->inline(false)
                ->disabled(! static::currentUserIsOwner()))
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $registry = app(ModuleRegistry::class);

        foreach ($registry->all()->keys() as $key) {
            $data["module_{$key}"] = $registry->isEnabled($key, $this->tenant);
        }

        return $data;
    }

    /**
     * Prekidači modula ne pripadaju tabeli domaćinstva — skidamo ih iz podataka
     * forme i upisujemo kroz registry (tabela `household_modules`).
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $registry = app(ModuleRegistry::class);

        foreach ($registry->all()->keys() as $key) {
            $field = "module_{$key}";

            if (array_key_exists($field, $data)) {
                $registry->setEnabled($this->tenant, $key, (bool) $data[$field]);
                unset($data[$field]);
            }
        }

        return $data;
    }

    /**
     * Puno osvježavanje nakon snimanja — meni i widgeti se renderuju u layoutu,
     * pa se promjena uključenosti modula inače ne bi vidjela do sljedeće navigacije.
     */
    protected function getRedirectUrl(): ?string
    {
        return filament()->getTenantProfileUrl(['tenant' => $this->tenant]);
    }

    /**
     * @return array<PageAction>
     */
    protected function getFormActions(): array
    {
        return static::currentUserIsOwner() ? parent::getFormActions() : [];
    }

    /**
     * "Pozovi člana" stoji uz samu listu članova (zaglavlje tabele), ne na vrhu
     * stranice — tamo je djelovalo kao radnja nad postavkama domaćinstva uopšte.
     */
    private function inviteAction(): Action
    {
        return Action::make('invite')
            ->label(__('platform.members.invite_action'))
            ->icon('heroicon-m-user-plus')
            ->modalHeading(__('platform.members.invite_modal_heading'))
            ->modalSubmitActionLabel(__('platform.members.invite_modal_submit'))
            // Član ne dovodi ljude u tuđe domaćinstvo (CLAUDE.md §1).
            ->visible(fn (): bool => static::currentUserIsOwner())
            ->form([
                TextInput::make('email')
                    ->label(__('platform.members.user'))
                    ->helperText(__('platform.members.user_helper'))
                    ->email()
                    ->required()
                    // Više se NE traži da osoba već ima nalog: ako ga nema, dobija
                    // pozivnicu s linkom (Faza 7c). Ostaje samo provjera da nije
                    // već član.
                    ->rule(fn () => function (string $attribute, $value, \Closure $fail): void {
                        $user = User::where('email', $value)->first();

                        if ($user && $this->tenant->users()->whereKey($user->id)->exists()) {
                            $fail(__('platform.members.already_member'));
                        }
                    }),
                Select::make('role')
                    ->label(__('platform.members.role'))
                    ->options([
                        'member' => __('platform.members.role_member'),
                        'owner' => __('platform.members.role_owner'),
                    ])
                    ->default('member')
                    ->required(),
            ])
            ->action(function (array $data): void {
                static::run(function () use ($data): void {
                    $addedImmediately = app(HouseholdInvitationService::class)->invite(
                        $this->tenant,
                        $data['email'],
                        $data['role'],
                        auth()->user(),
                    );

                    Notification::make()
                        ->title(__($addedImmediately
                            ? 'platform.members.added'
                            : 'platform.invitations.sent', ['email' => $data['email']]))
                        ->success()
                        ->send();
                });
            });
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('platform.members.plural_label'))
            ->headerActions([$this->inviteAction()])
            ->query(fn (): Builder => HouseholdMember::query()->where('household_id', $this->tenant->getKey()))
            ->columns([
                TextColumn::make('user.name')
                    ->label(__('platform.members.column_user'))
                    ->searchable(),
                TextColumn::make('user.email')
                    ->label(__('platform.members.column_email'))
                    ->searchable(),
                TextColumn::make('role')
                    ->label(__('platform.members.role'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __("platform.members.role_{$state}")),
                TextColumn::make('joined_at')
                    ->label(__('platform.members.joined_at'))
                    ->date('d.m.Y.'),
            ])
            ->actions([
                Action::make('changeRole')
                    ->label(__('platform.members.change_role'))
                    ->icon('heroicon-m-user-circle')
                    ->modalHeading(__('platform.members.change_role_heading'))
                    ->modalSubmitActionLabel(__('platform.members.change_role_submit'))
                    ->visible(fn (): bool => static::currentUserIsOwner())
                    ->fillForm(fn (HouseholdMember $record) => ['role' => $record->role])
                    ->form([
                        Select::make('role')
                            ->label(__('platform.members.role'))
                            ->options([
                                'member' => __('platform.members.role_member'),
                                'owner' => __('platform.members.role_owner'),
                            ])
                            ->required(),
                    ])
                    ->action(fn (HouseholdMember $record, array $data) => static::run(
                        fn () => app(HouseholdMemberService::class)->changeRole($record, $data['role']),
                    )),

                Action::make('transfer')
                    ->label(__('platform.members.transfer'))
                    ->icon('heroicon-m-key')
                    ->requiresConfirmation()
                    ->modalHeading(__('platform.members.transfer_heading'))
                    ->modalDescription(fn (HouseholdMember $record) => __('platform.members.transfer_description', ['name' => $record->user?->name ?? '—']))
                    ->modalSubmitActionLabel(__('platform.members.transfer_submit'))
                    ->visible(fn (HouseholdMember $record): bool => static::currentUserIsOwner() && ! static::isCurrentUser($record))
                    ->action(fn (HouseholdMember $record) => static::run(
                        fn () => app(HouseholdMemberService::class)->transferOwnership($record, static::currentMember()),
                    )),

                Action::make('remove')
                    ->label(__('platform.members.remove'))
                    ->icon('heroicon-m-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading(__('platform.members.remove_heading'))
                    ->modalDescription(fn (HouseholdMember $record) => __('platform.members.remove_description', ['name' => $record->user?->name ?? '—']))
                    ->visible(fn (HouseholdMember $record): bool => static::currentUserIsOwner() && ! static::isCurrentUser($record))
                    ->action(fn (HouseholdMember $record) => static::run(
                        fn () => app(HouseholdMemberService::class)->remove($record),
                    )),
            ])
            ->emptyStateHeading(__('platform.members.empty_state_heading'))
            ->emptyStateDescription(__('platform.members.empty_state_description'))
            ->emptyStateIcon('heroicon-o-users');
    }

    private static function currentMember(): ?HouseholdMember
    {
        $household = Filament::getTenant();

        return $household?->members()->where('user_id', auth()->id())->first();
    }

    public static function currentUserIsOwner(): bool
    {
        return static::currentMember()?->role === 'owner';
    }

    private static function isCurrentUser(HouseholdMember $record): bool
    {
        return $record->user_id === auth()->id();
    }

    /** Izvrši radnju servisa; guard greške (npr. zadnji vlasnik) prikaži kao notifikaciju. */
    private static function run(callable $action): void
    {
        try {
            $action();
        } catch (RuntimeException $e) {
            Notification::make()->title($e->getMessage())->danger()->send();
        }
    }
}
