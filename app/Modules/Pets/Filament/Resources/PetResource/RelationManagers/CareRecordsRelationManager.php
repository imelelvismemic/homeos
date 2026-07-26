<?php

namespace App\Modules\Pets\Filament\Resources\PetResource\RelationManagers;

use App\Modules\Pets\Enums\CareType;
use App\Modules\Pets\Models\CareRecord;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Njega se vodi uz ljubimca — vakcine, pregledi i terapije nemaju smisla kao
 * zasebna stavka menija. Datum unesen ovdje pokreće cijeli lanac platforme
 * (podsjetnik, kalendar, dashboard, sažetak).
 */
class CareRecordsRelationManager extends RelationManager
{
    protected static string $relationship = 'careRecords';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('pets.care.title');
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Select::make('type')
                ->label(__('pets.care.fields.type'))
                ->options(CareType::options())
                ->default(CareType::Vaccination->value)
                ->required(),

            DateTimePicker::make('due_date')
                ->label(__('pets.care.fields.due_date'))
                ->native(false)
                ->seconds(false)
                ->displayFormat('d.m.Y H:i')
                ->required(),

            TextInput::make('remind_days_before')
                ->label(__('pets.care.fields.remind_days_before'))
                ->helperText(__('pets.care.fields.remind_days_before_help'))
                ->numeric()
                ->minValue(0)
                ->default(3)
                ->required(),

            Textarea::make('notes')
                ->label(__('pets.care.fields.notes'))
                ->rows(2)
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('type')
            ->columns([
                TextColumn::make('type')
                    ->label(__('pets.care.fields.type'))
                    ->badge()
                    ->formatStateUsing(fn (CareType $state) => $state->label())
                    ->color(fn (CareType $state) => $state->color()),

                TextColumn::make('due_date')
                    ->label(__('pets.care.fields.due_date'))
                    ->dateTime('d.m.Y. H:i')
                    ->sortable()
                    ->color(fn (CareRecord $r) => $r->due_date->isPast() && ! $r->isDone() ? 'danger' : null),

                TextColumn::make('completed_at')
                    ->label(__('pets.care.fields.status'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? __('pets.care.status.done') : __('pets.care.status.planned'))
                    ->color(fn ($state) => $state ? 'gray' : 'warning'),
            ])
            ->defaultSort('due_date')
            ->headerActions([
                CreateAction::make()
                    ->label(__('pets.care.create'))
                    ->modalHeading(__('pets.care.create'))
                    ->mutateFormDataUsing(function (array $data): array {
                        // Njega nasljeđuje domaćinstvo i autora od ljubimca.
                        $data['household_id'] = $this->getOwnerRecord()->household_id;
                        $data['created_by'] = auth()->id();

                        return $data;
                    }),
            ])
            ->actions([
                Action::make('complete')
                    ->label(__('pets.care.complete'))
                    ->icon('heroicon-m-check')
                    ->color('success')
                    ->visible(fn (CareRecord $r) => ! $r->isDone())
                    ->action(fn (CareRecord $r) => $r->update(['completed_at' => now()])),
                EditAction::make(),
                DeleteAction::make()
                    ->modalHeading(__('pets.care.delete'))
                    ->modalDescription(fn (CareRecord $r) => __('pets.care.delete_description', ['title' => $r->displayTitle()])),
            ])
            ->emptyStateHeading(__('pets.care.empty'))
            ->emptyStateDescription(__('pets.care.empty_description'));
    }
}
