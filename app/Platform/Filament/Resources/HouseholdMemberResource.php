<?php

namespace App\Platform\Filament\Resources;

use App\Platform\Filament\Resources\HouseholdMemberResource\Pages\ListHouseholdMembers;
use App\Platform\Models\HouseholdMember;
use App\Platform\Services\HouseholdMemberService;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use RuntimeException;

class HouseholdMemberResource extends Resource
{
    protected static ?string $model = HouseholdMember::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function getModelLabel(): string
    {
        return __('platform.members.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('platform.members.plural_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('platform.members.navigation_label');
    }

    public static function table(Table $table): Table
    {
        return $table
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
                    ->date(),
            ])
            ->actions([
                Action::make('changeRole')
                    ->label(__('platform.members.change_role'))
                    ->icon('heroicon-m-user-circle')
                    ->modalHeading(__('platform.members.change_role_heading'))
                    ->modalSubmitActionLabel(__('platform.members.change_role_submit'))
                    ->visible(fn () => static::currentUserIsOwner())
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
                    ->action(function (HouseholdMember $record, array $data): void {
                        static::run(fn () => app(HouseholdMemberService::class)->changeRole($record, $data['role']));
                    }),

                Action::make('transfer')
                    ->label(__('platform.members.transfer'))
                    ->icon('heroicon-m-key')
                    ->requiresConfirmation()
                    ->modalHeading(__('platform.members.transfer_heading'))
                    ->modalDescription(fn (HouseholdMember $record) => __('platform.members.transfer_description', ['name' => $record->user?->name ?? '—']))
                    ->modalSubmitActionLabel(__('platform.members.transfer_submit'))
                    ->visible(fn (HouseholdMember $record) => static::currentUserIsOwner() && ! static::isCurrentUser($record))
                    ->action(function (HouseholdMember $record): void {
                        static::run(fn () => app(HouseholdMemberService::class)->transferOwnership($record, static::currentMember()));
                    }),

                Action::make('remove')
                    ->label(__('platform.members.remove'))
                    ->icon('heroicon-m-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading(__('platform.members.remove_heading'))
                    ->modalDescription(fn (HouseholdMember $record) => __('platform.members.remove_description', ['name' => $record->user?->name ?? '—']))
                    ->visible(fn (HouseholdMember $record) => static::currentUserIsOwner() && ! static::isCurrentUser($record))
                    ->action(function (HouseholdMember $record): void {
                        static::run(fn () => app(HouseholdMemberService::class)->remove($record));
                    }),
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

    private static function currentUserIsOwner(): bool
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

    public static function getPages(): array
    {
        return [
            'index' => ListHouseholdMembers::route('/'),
        ];
    }
}
