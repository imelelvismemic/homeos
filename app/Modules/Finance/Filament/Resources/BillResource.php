<?php

namespace App\Modules\Finance\Filament\Resources;

use App\Modules\Finance\Filament\Resources\BillResource\Pages;
use App\Modules\Finance\Models\Bill;
use App\Modules\Finance\Models\Category;
use App\Modules\Finance\Support\Money;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BillResource extends Resource
{
    protected static ?string $model = Bill::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-currency-euro';

    protected static ?string $tenantOwnershipRelationshipName = 'household';

    protected static ?int $navigationSort = 2;

    public static function getModelLabel(): string
    {
        return __('finance.bills.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('finance.bills.plural_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('finance.bills.navigation_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('finance.navigation_group');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->visibleTo(auth()->user());
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('title')
                ->label(__('finance.bills.fields.title'))
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),

            TextInput::make('amount')
                ->label(__('finance.bills.fields.amount'))
                ->numeric()->minValue(0)->step('0.01')->prefix('KM')->required(),

            DatePicker::make('due_date')
                ->label(__('finance.bills.fields.due_date'))
                ->native(false)->displayFormat('d.m.Y')->required(),

            Select::make('category_id')
                ->label(__('finance.bills.fields.category'))
                ->options(fn () => Category::query()->where('household_id', Filament::getTenant()?->id)->pluck('name', 'id'))
                ->searchable()
                ->createOptionForm(CategoryResource::formSchema())
                ->createOptionUsing(fn (array $data) => CategoryResource::createOption($data)),

            TextInput::make('remind_days_before')
                ->label(__('finance.bills.fields.remind_days_before'))
                ->helperText(__('finance.bills.fields.remind_days_before_help'))
                ->numeric()->minValue(0)->default(3)->required(),

            Select::make('recurrence')
                ->label(__('finance.bills.fields.recurrence'))
                ->options([
                    'none' => __('finance.recurrence.none'),
                    'monthly' => __('finance.recurrence.monthly'),
                    'yearly' => __('finance.recurrence.yearly'),
                    'weekly' => __('finance.recurrence.weekly'),
                ])
                ->default('none')
                ->dehydrated(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label(__('finance.bills.fields.title'))->searchable()->weight('medium'),
                TextColumn::make('category.name')->label(__('finance.bills.fields.category'))->placeholder('—')->searchable()->toggleable(),
                TextColumn::make('amount')->label(__('finance.bills.fields.amount'))->formatStateUsing(fn ($state) => Money::km($state))->sortable(),
                TextColumn::make('due_date')
                    ->label(__('finance.bills.fields.due_date'))
                    ->date('d.m.Y.')
                    ->sortable()
                    ->color(fn (Bill $r) => $r->due_date && $r->due_date->isPast() && ! $r->isPaid() ? 'danger' : null),
                IconColumn::make('paid_at')
                    ->label(__('finance.bills.fields.paid'))
                    ->boolean()
                    ->state(fn (Bill $r) => $r->isPaid()),
            ])
            ->defaultSort('due_date')
            ->filters([
                Filter::make('unpaid')
                    ->label(__('finance.bills.filters.unpaid'))
                    ->query(fn (Builder $query) => $query->whereNull('paid_at'))
                    ->default(),
                SelectFilter::make('category_id')
                    ->label(__('finance.bills.fields.category'))
                    ->options(fn () => Category::query()->where('household_id', Filament::getTenant()?->id)->pluck('name', 'id')),
            ])
            ->actions([
                Action::make('markPaid')
                    ->label(__('finance.bills.actions.mark_paid'))
                    ->icon('heroicon-m-check')
                    ->color('success')
                    ->visible(fn (Bill $r) => ! $r->isPaid())
                    ->requiresConfirmation()
                    ->action(fn (Bill $r) => $r->update(['paid_at' => now()])),
                EditAction::make(),
                DeleteAction::make()
                    ->modalHeading(__('finance.bills.delete'))
                    ->modalDescription(fn (Bill $r) => __('finance.bills.delete_description', ['title' => $r->title])),
            ])
            ->emptyStateHeading(__('finance.bills.empty.heading'))
            ->emptyStateDescription(__('finance.bills.empty.description'))
            ->emptyStateIcon('heroicon-o-document-currency-euro');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBills::route('/'),
            'create' => Pages\CreateBill::route('/create'),
            'edit' => Pages\EditBill::route('/{record}/edit'),
        ];
    }
}
