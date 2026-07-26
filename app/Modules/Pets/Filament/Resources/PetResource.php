<?php

namespace App\Modules\Pets\Filament\Resources;

use App\Modules\Pets\Enums\PetSpecies;
use App\Modules\Pets\Filament\Resources\PetResource\Pages;
use App\Modules\Pets\Filament\Resources\PetResource\RelationManagers\CareRecordsRelationManager;
use App\Modules\Pets\Models\Pet;
use App\Platform\Filament\Resources\ModuleResource;
use App\Platform\Filament\Sharing\SharingForm;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PetResource extends ModuleResource
{
    protected static ?string $model = Pet::class;

    protected static ?string $navigationIcon = 'heroicon-o-heart';

    protected static ?int $navigationSort = 4;

    // Tenancy preko relacije na zapisu (Pet->household), ne na tenantu (CLAUDE.md §4).
    protected static ?string $tenantOwnershipRelationshipName = 'household';

    public static function getModelLabel(): string
    {
        return __('pets.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('pets.plural_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('pets.navigation_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('pets.navigation_group');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->visibleTo(auth()->user());
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')
                ->label(__('pets.fields.name'))
                ->required()
                ->maxLength(255),

            Select::make('species')
                ->label(__('pets.fields.species'))
                ->options(PetSpecies::options())
                ->default(PetSpecies::Dog->value)
                ->required(),

            DatePicker::make('birth_date')
                ->label(__('pets.fields.birth_date'))
                ->native(false)
                ->displayFormat('d.m.Y')
                ->maxDate(now())
                ->helperText(__('pets.fields.birth_date_help')),

            Textarea::make('notes')
                ->label(__('pets.fields.notes'))
                ->rows(3)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('pets.fields.name'))
                    ->searchable()
                    ->weight('medium'),

                TextColumn::make('species')
                    ->label(__('pets.fields.species'))
                    ->badge()
                    ->formatStateUsing(fn (PetSpecies $state) => $state->label()),

                TextColumn::make('birth_date')
                    ->label(__('pets.fields.birth_date'))
                    ->date('d.m.Y.')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('care_records_count')
                    ->label(__('pets.fields.care_count'))
                    ->counts('careRecords')
                    ->toggleable(),
            ])
            ->defaultSort('name')
            ->filters([
                SelectFilter::make('species')
                    ->label(__('pets.fields.species'))
                    ->options(PetSpecies::options()),
            ])
            ->actions([
                EditAction::make(),
                SharingForm::tableAction(),
                DeleteAction::make()
                    ->modalHeading(__('pets.headings.delete'))
                    ->modalDescription(fn (Pet $r) => __('pets.headings.delete_description', ['name' => $r->name])),
            ])
            ->emptyStateHeading(__('pets.empty.heading'))
            ->emptyStateDescription(__('pets.empty.description'))
            ->emptyStateIcon('heroicon-o-heart');
    }

    public static function getRelations(): array
    {
        return [CareRecordsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPets::route('/'),
            'create' => Pages\CreatePet::route('/create'),
            'edit' => Pages\EditPet::route('/{record}/edit'),
        ];
    }
}
