<?php

namespace App\Modules\Reminders\Filament\Resources;

use App\Modules\Reminders\Filament\Resources\ReminderResource\Pages;
use App\Modules\Reminders\Models\Reminder;
use App\Platform\Models\HouseholdMember;
use Filament\Facades\Filament;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReminderResource extends Resource
{
    protected static ?string $model = Reminder::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell';

    // Tenancy preko relacije na zapisu (Reminder->household), ne na tenantu (§4).
    protected static ?string $tenantOwnershipRelationshipName = 'household';

    public static function getModelLabel(): string
    {
        return __('reminders.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('reminders.plural_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('reminders.navigation_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('reminders.navigation_group');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->visibleTo(auth()->user());
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('title')
                ->label(__('reminders.fields.title'))
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),

            Textarea::make('description')
                ->label(__('reminders.fields.description'))
                ->rows(3)
                ->columnSpanFull(),

            DateTimePicker::make('due_date')
                ->label(__('reminders.fields.due_date'))
                ->native(false)
                ->seconds(false)
                ->displayFormat('d.m.Y H:i')
                ->required()
                ->suffixAction(
                    FormAction::make('now')
                        ->label(__('reminders.fields.due_date_now'))
                        ->icon('heroicon-m-clock')
                        ->action(fn (Set $set) => $set('due_date', now())),
                ),

            Select::make('recurrence')
                ->label(__('reminders.fields.recurrence'))
                ->options([
                    'none' => __('reminders.recurrence.none'),
                    'daily' => __('reminders.recurrence.daily'),
                    'weekly' => __('reminders.recurrence.weekly'),
                    'monthly' => __('reminders.recurrence.monthly'),
                    'yearly' => __('reminders.recurrence.yearly'),
                ])
                ->default('none')
                ->dehydrated(false),

            Select::make('assigned_to')
                ->label(__('reminders.fields.assigned_to'))
                ->options(fn () => HouseholdMember::query()
                    ->where('household_id', Filament::getTenant()?->id)
                    ->with('user')
                    ->get()
                    ->pluck('user.name', 'id'))
                ->searchable()
                ->helperText(__('reminders.fields.assigned_to_help')),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('reminders.fields.title'))
                    ->searchable()
                    ->weight('medium')
                    ->description(fn (Reminder $r) => $r->remindable?->title),

                TextColumn::make('due_date')
                    ->label(__('reminders.fields.due_date'))
                    ->dateTime('d.m.Y. H:i')
                    ->sortable()
                    ->color(fn (Reminder $r) => $r->due_date && $r->due_date->isPast() && ! $r->completed_at ? 'danger' : null),

                TextColumn::make('assignee.user.name')
                    ->label(__('reminders.fields.assigned_to'))
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('completed_at')
                    ->label(__('reminders.fields.status'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? __('reminders.status.done') : __('reminders.status.pending'))
                    ->color(fn ($state) => $state ? 'gray' : 'warning'),
            ])
            ->defaultSort('due_date')
            ->filters([
                Filter::make('hide_done')
                    ->label(__('reminders.filters.hide_done'))
                    ->query(fn (Builder $query) => $query->whereNull('completed_at'))
                    ->default(),
            ])
            ->actions([
                Action::make('complete')
                    ->label(__('reminders.actions.complete'))
                    ->icon('heroicon-m-check')
                    ->color('success')
                    ->visible(fn (Reminder $r) => $r->completed_at === null)
                    ->action(fn (Reminder $r) => $r->update(['completed_at' => now()])),
                EditAction::make(),
                DeleteAction::make()
                    ->modalHeading(__('reminders.headings.delete'))
                    ->modalDescription(fn (Reminder $r) => __('reminders.headings.delete_description', ['title' => $r->title])),
            ])
            ->emptyStateHeading(__('reminders.empty.heading'))
            ->emptyStateDescription(__('reminders.empty.description'))
            ->emptyStateIcon('heroicon-o-bell');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReminders::route('/'),
            'create' => Pages\CreateReminder::route('/create'),
            'edit' => Pages\EditReminder::route('/{record}/edit'),
        ];
    }
}
