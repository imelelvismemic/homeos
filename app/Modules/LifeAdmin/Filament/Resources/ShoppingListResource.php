<?php

namespace App\Modules\LifeAdmin\Filament\Resources;

use App\Modules\LifeAdmin\Filament\Resources\ShoppingListResource\Pages;
use App\Modules\LifeAdmin\Filament\Resources\ShoppingListResource\RelationManagers\ItemsRelationManager;
use App\Modules\LifeAdmin\Models\ShoppingList;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ShoppingListResource extends Resource
{
    protected static ?string $model = ShoppingList::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $tenantOwnershipRelationshipName = 'household';

    protected static ?int $navigationSort = 3;

    public static function getModelLabel(): string
    {
        return __('lifeadmin.lists.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('lifeadmin.lists.plural_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('lifeadmin.lists.navigation_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('lifeadmin.navigation_group');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->visibleTo(auth()->user());
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')
                ->label(__('lifeadmin.lists.fields.name'))
                ->required()
                ->maxLength(255),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label(__('lifeadmin.lists.fields.name'))->searchable()->weight('medium'),
                TextColumn::make('open_items')
                    ->label(__('lifeadmin.lists.fields.open_items'))
                    ->badge()
                    ->state(fn (ShoppingList $r) => $r->items()->where('is_done', false)->count()),
            ])
            ->defaultSort('name')
            ->actions([
                EditAction::make(),
                DeleteAction::make()
                    ->modalHeading(__('lifeadmin.lists.delete'))
                    ->modalDescription(fn (ShoppingList $r) => __('lifeadmin.lists.delete_description', ['name' => $r->name])),
            ])
            ->emptyStateHeading(__('lifeadmin.lists.empty.heading'))
            ->emptyStateDescription(__('lifeadmin.lists.empty.description'))
            ->emptyStateIcon('heroicon-o-shopping-cart');
    }

    public static function getRelations(): array
    {
        return [ItemsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShoppingLists::route('/'),
            'create' => Pages\CreateShoppingList::route('/create'),
            'edit' => Pages\EditShoppingList::route('/{record}/edit'),
        ];
    }
}
