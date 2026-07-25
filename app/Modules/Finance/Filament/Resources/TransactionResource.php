<?php

namespace App\Modules\Finance\Filament\Resources;

use App\Modules\Finance\Enums\TransactionType;
use App\Modules\Finance\Filament\Resources\TransactionResource\Pages;
use App\Modules\Finance\Models\Category;
use App\Modules\Finance\Models\Transaction;
use App\Modules\Finance\Support\Money;
use App\Platform\Models\HouseholdMember;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $tenantOwnershipRelationshipName = 'household';

    protected static ?int $navigationSort = 1;

    public static function getModelLabel(): string
    {
        return __('finance.transactions.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('finance.transactions.plural_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('finance.transactions.navigation_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('finance.navigation_group');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->visibleTo(auth()->user());
    }

    /**
     * @return array<int|string, string>
     */
    public static function memberOptions(): array
    {
        return HouseholdMember::query()
            ->where('household_id', Filament::getTenant()?->id)
            ->with('user')
            ->get()
            ->pluck('user.name', 'id')
            ->all();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('type')
                ->label(__('finance.transactions.fields.type'))
                ->options(fn () => collect(TransactionType::cases())->mapWithKeys(fn ($t) => [$t->value => $t->label()]))
                ->default(TransactionType::Expense->value)
                ->required()
                ->live(),

            TextInput::make('title')
                ->label(__('finance.transactions.fields.title'))
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),

            TextInput::make('amount')
                ->label(__('finance.transactions.fields.amount'))
                ->numeric()
                ->minValue(0)
                ->step('0.01')
                ->prefix('KM')
                ->required(),

            DatePicker::make('date')
                ->label(__('finance.transactions.fields.date'))
                ->native(false)
                ->displayFormat('d.m.Y')
                ->default(now())
                ->required(),

            Select::make('category_id')
                ->label(__('finance.transactions.fields.category'))
                ->options(fn () => Category::query()->where('household_id', Filament::getTenant()?->id)->pluck('name', 'id'))
                ->searchable()
                ->createOptionForm(CategoryResource::formSchema())
                ->createOptionUsing(fn (array $data) => CategoryResource::createOption($data)),

            Select::make('paid_by')
                ->label(__('finance.transactions.fields.paid_by'))
                ->options(fn () => static::memberOptions())
                ->searchable()
                ->visible(fn (callable $get) => $get('type') === TransactionType::Expense->value),

            Select::make('participants')
                ->label(__('finance.transactions.fields.participants'))
                ->helperText(__('finance.transactions.fields.participants_help'))
                ->multiple()
                ->options(fn () => static::memberOptions())
                ->dehydrated(false)
                ->visible(fn (callable $get) => $get('type') === TransactionType::Expense->value)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')->label(__('finance.transactions.fields.date'))->date('d.m.Y.')->sortable(),
                TextColumn::make('type')
                    ->label(__('finance.transactions.fields.type'))
                    ->badge()
                    ->formatStateUsing(fn (TransactionType $state) => $state->label())
                    ->color(fn (TransactionType $state) => $state->color()),
                TextColumn::make('title')->label(__('finance.transactions.fields.title'))->searchable()->weight('medium'),
                TextColumn::make('category.name')->label(__('finance.transactions.fields.category'))->placeholder('—')->toggleable(),
                TextColumn::make('amount')
                    ->label(__('finance.transactions.fields.amount'))
                    ->formatStateUsing(fn ($state) => Money::km($state))
                    ->sortable()
                    ->color(fn (Transaction $r) => $r->type->color()),
                TextColumn::make('payer.user.name')->label(__('finance.transactions.fields.paid_by'))->placeholder('—')->toggleable(),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->label(__('finance.transactions.fields.type'))
                    ->options(fn () => collect(TransactionType::cases())->mapWithKeys(fn ($t) => [$t->value => $t->label()])),
                Filter::make('this_month')
                    ->label(__('finance.transactions.filters.this_month'))
                    ->query(fn (Builder $query) => $query->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()])),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make()
                    ->modalHeading(__('finance.transactions.delete'))
                    ->modalDescription(fn (Transaction $r) => __('finance.transactions.delete_description', ['title' => $r->title])),
            ])
            ->emptyStateHeading(__('finance.transactions.empty.heading'))
            ->emptyStateDescription(__('finance.transactions.empty.description'))
            ->emptyStateIcon('heroicon-o-banknotes');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
