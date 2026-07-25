<?php

namespace App\Modules\Finance\Filament\Resources;

use App\Modules\Finance\Filament\Resources\BudgetResource\Pages;
use App\Modules\Finance\Models\Budget;
use App\Modules\Finance\Models\Category;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;

class BudgetResource extends Resource
{
    protected static ?string $model = Budget::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';

    protected static ?string $tenantOwnershipRelationshipName = 'household';

    protected static ?int $navigationSort = 3;

    public static function getModelLabel(): string
    {
        return __('finance.budgets.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('finance.budgets.plural_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('finance.budgets.navigation_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('finance.navigation_group');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('category_id')
                ->label(__('finance.budgets.fields.category'))
                ->options(fn () => Category::query()->where('household_id', Filament::getTenant()?->id)->pluck('name', 'id'))
                ->searchable()
                ->required(),

            DatePicker::make('month')
                ->label(__('finance.budgets.fields.month'))
                ->native(false)
                ->displayFormat('m.Y.')
                ->default(now()->startOfMonth())
                ->required()
                // Uvijek prvi dan mjeseca (unique po household+kategorija+mjesec).
                ->dehydrateStateUsing(fn ($state) => Carbon::parse($state)->startOfMonth()->toDateString()),

            TextInput::make('amount')
                ->label(__('finance.budgets.fields.amount'))
                ->numeric()->minValue(0)->step('0.01')->prefix('KM')->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('category.name')->label(__('finance.budgets.fields.category'))->searchable()->weight('medium'),
                TextColumn::make('month')->label(__('finance.budgets.fields.month'))->date('m.Y.')->sortable(),
                TextColumn::make('amount')->label(__('finance.budgets.fields.amount'))->money('BAM')->sortable(),
            ])
            ->defaultSort('month', 'desc')
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->emptyStateHeading(__('finance.budgets.empty.heading'))
            ->emptyStateDescription(__('finance.budgets.empty.description'))
            ->emptyStateIcon('heroicon-o-chart-pie');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBudgets::route('/'),
            'create' => Pages\CreateBudget::route('/create'),
            'edit' => Pages\EditBudget::route('/{record}/edit'),
        ];
    }
}
