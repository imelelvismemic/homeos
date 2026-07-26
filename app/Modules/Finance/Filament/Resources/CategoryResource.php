<?php

namespace App\Modules\Finance\Filament\Resources;

use App\Modules\Finance\Filament\Resources\CategoryResource\Pages;
use App\Modules\Finance\Models\Category;
use App\Platform\Filament\Resources\ModuleResource;
use Filament\Facades\Filament;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CategoryResource extends ModuleResource
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
        return $form->schema(static::formSchema());
    }

    /**
     * Dijeljena šema — koristi je i "brzo dodaj kategoriju" iz forme računa/transakcije.
     *
     * @return array<int, Component>
     */
    public static function formSchema(): array
    {
        return [
            TextInput::make('name')
                ->label(__('finance.categories.fields.name'))
                ->required()
                ->maxLength(255),

            ColorPicker::make('color')
                ->label(__('finance.categories.fields.color')),
        ];
    }

    /** Kreira kategoriju iz inline "createOption" forme (household/creator stamp). */
    public static function createOption(array $data): int
    {
        return Category::create([
            'household_id' => Filament::getTenant()?->getKey(),
            'created_by' => auth()->id(),
            'name' => $data['name'],
            'color' => $data['color'] ?? null,
        ])->getKey();
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
