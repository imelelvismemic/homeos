<?php

namespace App\Modules\Tasks\Filament\Resources;

use App\Modules\Tasks\Enums\Priority;
use App\Modules\Tasks\Enums\TaskStatus;
use App\Modules\Tasks\Filament\Resources\TaskResource\Pages;
use App\Modules\Tasks\Models\Board;
use App\Modules\Tasks\Models\Task;
use App\Platform\Models\HouseholdMember;
use Filament\Facades\Filament;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
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
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-circle';

    // Tenancy skoping ide preko relacije NA zapisu (Task->household), a ne preko
    // relacije na tenantu — Platform (Household) ne smije znati za module (§4).
    protected static ?string $tenantOwnershipRelationshipName = 'household';

    public static function getModelLabel(): string
    {
        return __('tasks.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('tasks.plural_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('tasks.navigation_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('tasks.navigation_group');
    }

    /** Unutar domaćinstva (Filament tenancy) dodatno se skriva tuđe privatno. */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->visibleTo(auth()->user());
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('title')
                ->label(__('tasks.fields.title'))
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),

            Textarea::make('description')
                ->label(__('tasks.fields.description'))
                ->rows(3)
                ->columnSpanFull(),

            Select::make('priority')
                ->label(__('tasks.fields.priority'))
                ->options(fn () => collect(Priority::cases())->mapWithKeys(fn ($p) => [$p->value => $p->label()]))
                ->default(Priority::Medium->value)
                ->required(),

            Select::make('status')
                ->label(__('tasks.fields.status'))
                ->options(fn () => collect(TaskStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()]))
                ->default(TaskStatus::Todo->value)
                ->required(),

            DateTimePicker::make('due_date')
                ->label(__('tasks.fields.due_date'))
                ->native(false)
                ->seconds(false)
                ->displayFormat('d.m.Y H:i')
                ->suffixAction(
                    FormAction::make('now')
                        ->label(__('tasks.fields.due_date_now'))
                        ->icon('heroicon-m-clock')
                        ->action(fn (Set $set) => $set('due_date', now())),
                ),

            Select::make('recurrence')
                ->label(__('tasks.fields.recurrence'))
                ->options([
                    'none' => __('tasks.recurrence.none'),
                    'daily' => __('tasks.recurrence.daily'),
                    'weekly' => __('tasks.recurrence.weekly'),
                    'monthly' => __('tasks.recurrence.monthly'),
                    'yearly' => __('tasks.recurrence.yearly'),
                ])
                ->default('none')
                ->dehydrated(false),

            Select::make('assigned_to')
                ->label(__('tasks.fields.assigned_to'))
                ->options(fn () => static::householdMemberOptions())
                ->searchable(),

            Select::make('board_id')
                ->label(__('tasks.fields.board'))
                ->options(fn () => Board::query()->where('household_id', Filament::getTenant()?->id)->pluck('name', 'id'))
                ->searchable()
                // Tabla se kreira "u hodu" iz forme (dropdown je prazan dok ne
                // postoji nijedna tabla) — bez odlaska na poseban ekran.
                ->createOptionForm([
                    TextInput::make('name')
                        ->label(__('tasks.kanban.board_name'))
                        ->required()
                        ->maxLength(255),
                ])
                ->createOptionUsing(fn (array $data): int => Board::create([
                    'household_id' => Filament::getTenant()?->id,
                    'created_by' => auth()->id(),
                    'name' => $data['name'],
                ])->getKey()),

            Select::make('parent_task_id')
                ->label(__('tasks.fields.parent'))
                ->options(fn (?Task $record) => Task::query()
                    ->where('household_id', Filament::getTenant()?->id)
                    ->when($record, fn ($q) => $q->whereKeyNot($record->id))
                    ->pluck('title', 'id'))
                ->searchable(),

            TagsInput::make('tags')
                ->label(__('tasks.fields.tags'))
                ->dehydrated(false)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('tasks.fields.title'))
                    ->searchable()
                    ->weight('medium')
                    ->description(fn (Task $r) => $r->parent?->title),

                TextColumn::make('status')
                    ->label(__('tasks.fields.status'))
                    ->badge()
                    ->formatStateUsing(fn (TaskStatus $state) => $state->label())
                    ->color(fn (TaskStatus $state) => $state->color()),

                TextColumn::make('priority')
                    ->label(__('tasks.fields.priority'))
                    ->badge()
                    ->formatStateUsing(fn (Priority $state) => $state->label())
                    ->color(fn (Priority $state) => $state->color()),

                TextColumn::make('due_date')
                    ->label(__('tasks.fields.due_date'))
                    ->dateTime('d.m.Y. H:i')
                    ->sortable()
                    ->toggleable()
                    ->color(fn (Task $r) => $r->due_date && $r->due_date->isPast() && ! $r->completed_at ? 'danger' : null),

                TextColumn::make('assignee.user.name')
                    ->label(__('tasks.fields.assigned_to'))
                    ->toggleable()
                    // Pretraga tabele po imenu/prezimenu odgovorne osobe.
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->orWhereHas(
                        'assignee.user',
                        fn (Builder $q) => $q->where('name', 'like', "%{$search}%"),
                    ))
                    ->placeholder('—'),

                TextColumn::make('tags.name')
                    ->label(__('tasks.fields.tags'))
                    ->badge()
                    ->separator(',')
                    ->toggleable()
                    // Pretraga tabele po nazivima dodijeljenih oznaka.
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->orWhereHas(
                        'tags',
                        fn (Builder $q) => $q->where('name', 'like', "%{$search}%"),
                    )),
            ])
            ->defaultSort('due_date')
            ->filters([
                // NB: Filament ubrizgava upit u filter closure PO IMENU parametra
                // (`$query`), ne po tipu — parametar se MORA zvati $query, inače
                // filter tiho ne radi ništa.
                TernaryFilter::make('assigned_to_me')
                    ->label(__('tasks.filters.only_mine'))
                    ->queries(
                        true: fn (Builder $query) => $query->whereHas('assignee', fn (Builder $q) => $q->where('user_id', auth()->id())),
                        false: fn (Builder $query) => $query,
                        blank: fn (Builder $query) => $query,
                    ),
                Filter::make('overdue')
                    ->label(__('tasks.filters.overdue'))
                    ->query(fn (Builder $query) => $query->whereNull('completed_at')->whereNotNull('due_date')->where('due_date', '<', now())),
                Filter::make('hide_done')
                    ->label(__('tasks.filters.hide_done'))
                    ->query(fn (Builder $query) => $query->where('status', '!=', TaskStatus::Done->value))
                    ->default(),
            ])
            ->actions([
                Action::make('complete')
                    ->label(__('tasks.actions.complete'))
                    ->icon('heroicon-m-check')
                    ->color('success')
                    ->visible(fn (Task $r) => $r->status !== TaskStatus::Done)
                    ->action(fn (Task $r) => $r->update(['status' => TaskStatus::Done])),
                EditAction::make(),
                DeleteAction::make()
                    ->modalHeading(__('tasks.headings.delete'))
                    ->modalDescription(fn (Task $r) => __('tasks.headings.delete_description', ['title' => $r->title])),
            ])
            ->emptyStateHeading(__('tasks.empty.heading'))
            ->emptyStateDescription(__('tasks.empty.description'))
            ->emptyStateIcon('heroicon-o-check-circle');
    }

    /**
     * @return array<int|string, string>
     */
    public static function householdMemberOptions(): array
    {
        return HouseholdMember::query()
            ->where('household_id', Filament::getTenant()?->id)
            ->with('user')
            ->get()
            ->pluck('user.name', 'id')
            ->all();
    }

    public static function getRelations(): array
    {
        return [
            TaskResource\RelationManagers\SubtasksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
        ];
    }
}
