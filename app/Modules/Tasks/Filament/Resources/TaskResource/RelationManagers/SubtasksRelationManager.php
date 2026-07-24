<?php

namespace App\Modules\Tasks\Filament\Resources\TaskResource\RelationManagers;

use App\Modules\Tasks\Enums\TaskStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class SubtasksRelationManager extends RelationManager
{
    protected static string $relationship = 'subtasks';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('tasks.subtasks.title');
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('title')
                ->label(__('tasks.fields.title'))
                ->required()
                ->maxLength(255),

            Select::make('status')
                ->label(__('tasks.fields.status'))
                ->options(fn () => collect(TaskStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()]))
                ->default(TaskStatus::Todo->value)
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')->label(__('tasks.fields.title')),
                TextColumn::make('status')
                    ->label(__('tasks.fields.status'))
                    ->badge()
                    ->formatStateUsing(fn (TaskStatus $state) => $state->label())
                    ->color(fn (TaskStatus $state) => $state->color()),
            ])
            ->headerActions([
                CreateAction::make()->mutateFormDataUsing(function (array $data): array {
                    // Podzadatak nasljeđuje domaćinstvo i autora od roditelja.
                    $data['household_id'] = $this->getOwnerRecord()->household_id;
                    $data['created_by'] = auth()->id();

                    return $data;
                }),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->emptyStateHeading(__('tasks.subtasks.empty'));
    }
}
