<?php

namespace App\Platform\Filament\Resources;

use App\Platform\Filament\Resources\HouseholdMemberResource\Pages\ListHouseholdMembers;
use App\Platform\Models\HouseholdMember;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

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
                    ->label(__('platform.members.label'))
                    ->searchable(),
                TextColumn::make('user.email')
                    ->searchable(),
                TextColumn::make('role')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __("platform.members.role_{$state}")),
                TextColumn::make('joined_at')
                    ->label(__('platform.members.joined_at'))
                    ->date(),
            ])
            ->emptyStateHeading(__('platform.members.empty_state_heading'))
            ->emptyStateDescription(__('platform.members.empty_state_description'))
            ->emptyStateIcon('heroicon-o-users');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHouseholdMembers::route('/'),
        ];
    }
}
