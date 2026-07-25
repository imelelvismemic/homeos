<?php

namespace App\Platform\Filament\Tenancy;

use App\Models\User;
use App\Platform\Models\HouseholdMember;
use App\Platform\Services\HouseholdMemberService;
use Filament\Actions\Action as PageAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
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
        ]);
    }

    /**
     * @return array<PageAction>
     */
    protected function getFormActions(): array
    {
        return static::currentUserIsOwner() ? parent::getFormActions() : [];
    }

    /**
     * @return array<PageAction>
     */
    protected function getHeaderActions(): array
    {
        return [
            PageAction::make('invite')
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
                        ->exists(table: 'users', column: 'email')
                        ->validationMessages(['exists' => __('platform.members.user_not_found')])
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
                    $user = User::where('email', $data['email'])->firstOrFail();

                    $this->tenant->members()->create([
                        'user_id' => $user->id,
                        'role' => $data['role'],
                        'joined_at' => now(),
                    ]);
                }),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('platform.members.plural_label'))
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
