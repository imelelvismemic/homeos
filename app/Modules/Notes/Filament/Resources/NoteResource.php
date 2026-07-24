<?php

namespace App\Modules\Notes\Filament\Resources;

use App\Modules\Notes\Filament\Resources\NoteResource\Pages;
use App\Modules\Notes\Models\Note;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class NoteResource extends Resource
{
    protected static ?string $model = Note::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $tenantOwnershipRelationshipName = 'household';

    public static function getModelLabel(): string
    {
        return __('notes.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('notes.plural_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('notes.navigation_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('notes.navigation_group');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->visibleTo(auth()->user());
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('title')
                ->label(__('notes.fields.title'))
                ->maxLength(255)
                ->columnSpanFull(),

            RichEditor::make('body')
                ->label(__('notes.fields.body'))
                ->required()
                ->columnSpanFull(),

            DatePicker::make('journal_date')
                ->label(__('notes.fields.journal_date'))
                ->native(false)
                ->displayFormat('d.m.Y')
                ->helperText(__('notes.fields.journal_date_help')),

            TagsInput::make('tags')
                ->label(__('notes.fields.tags'))
                ->dehydrated(false)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('notes.fields.title'))
                    ->getStateUsing(fn (Note $r) => $r->displayTitle())
                    ->searchable()
                    ->weight('medium')
                    ->wrap(),

                TextColumn::make('journal_date')
                    ->label(__('notes.fields.journal_date'))
                    ->date('d.m.Y.')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('tags.name')
                    ->label(__('notes.fields.tags'))
                    ->badge()
                    ->separator(',')
                    ->toggleable(),

                TextColumn::make('updated_at')
                    ->label(__('notes.fields.updated_at'))
                    ->since()
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                Filter::make('journal_only')
                    ->label(__('notes.filters.journal_only'))
                    ->query(fn (Builder $query) => $query->whereNotNull('journal_date')),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->emptyStateHeading(__('notes.empty.heading'))
            ->emptyStateDescription(__('notes.empty.description'))
            ->emptyStateIcon('heroicon-o-document-text');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNotes::route('/'),
            'create' => Pages\CreateNote::route('/create'),
            'edit' => Pages\EditNote::route('/{record}/edit'),
        ];
    }
}
