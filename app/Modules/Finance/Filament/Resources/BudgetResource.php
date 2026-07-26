<?php

namespace App\Modules\Finance\Filament\Resources;

use App\Modules\Finance\Filament\Resources\BudgetResource\Pages;
use App\Modules\Finance\Models\Budget;
use App\Modules\Finance\Models\Category;
use App\Modules\Finance\Support\Money;
use App\Platform\Filament\Concerns\BelongsToModule;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class BudgetResource extends Resource
{
    use BelongsToModule;

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

    /**
     * Mjeseci za odabir (prvi dan mjeseca → naziv), raspon oko sadašnjeg.
     *
     * @return array<string, string>
     */
    public static function monthOptions(): array
    {
        $options = [];
        $start = now()->startOfMonth()->subMonths(18);

        for ($i = 0; $i < 31; $i++) {
            $month = $start->copy()->addMonths($i);
            $options[$month->toDateString()] = Str::ucfirst($month->translatedFormat('F Y.'));
        }

        return $options;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('category_id')
                ->label(__('finance.budgets.fields.category'))
                ->options(fn () => Category::query()->where('household_id', Filament::getTenant()?->id)->pluck('name', 'id'))
                ->searchable()
                ->createOptionForm(CategoryResource::formSchema())
                ->createOptionUsing(fn (array $data) => CategoryResource::createOption($data))
                ->required(),

            // Izbor mjeseca/godine (bez nepotrebnog odabira dana) — vrijednost je
            // prvi dan mjeseca (unique po household+kategorija+mjesec).
            Select::make('month')
                ->label(__('finance.budgets.fields.month'))
                ->options(fn () => static::monthOptions())
                ->default(now()->startOfMonth()->toDateString())
                ->required(),

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
                TextColumn::make('amount')->label(__('finance.budgets.fields.amount'))->formatStateUsing(fn ($state) => Money::km($state))->sortable(),
            ])
            ->defaultSort('month', 'desc')
            ->actions([
                EditAction::make(),
                DeleteAction::make()
                    ->modalHeading(__('finance.budgets.delete'))
                    ->modalDescription(fn (Budget $r) => __('finance.budgets.delete_description', [
                        'category' => $r->category?->name ?? '—',
                        'month' => $r->month?->translatedFormat('F Y.') ?? '',
                    ])),
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
