<?php

namespace App\Modules\Finance\Filament\Resources;

use App\Modules\Finance\Filament\Resources\CategoryResource\Pages;
use App\Modules\Finance\Models\Category;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $tenantOwnershipRelationshipName = 'household';

    protected static ?int $navigationSort = 4;

    public static function getModelLabel(): string
    {
        return __('finance.categories.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('finance.categories.plural_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('finance.categories.navigation_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('finance.navigation_group');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')
                ->label(__('finance.categories.fields.name'))
                ->required()
                ->maxLength(255),

            ColorPicker::make('color')
                ->label(__('finance.categories.fields.color')),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ColorColumn::make('color')->label(__('finance.categories.fields.color')),
                TextColumn::make('name')->label(__('finance.categories.fields.name'))->searchable()->weight('medium'),
                TextColumn::make('transactions_count')->counts('transactions')->label(__('finance.categories.fields.transactions')),
            ])
            ->defaultSort('name')
            ->actions([
                EditAction::make(),
                DeleteAction::make()
                    ->modalHeading(__('finance.categories.delete'))
                    ->modalDescription(fn (Category $r) => __('finance.categories.delete_description', ['name' => $r->name])),
            ])
            ->emptyStateHeading(__('finance.categories.empty.heading'))
            ->emptyStateDescription(__('finance.categories.empty.description'))
            ->emptyStateIcon('heroicon-o-tag');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
