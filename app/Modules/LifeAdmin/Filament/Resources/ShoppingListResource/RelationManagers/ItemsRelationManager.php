<?php

namespace App\Modules\LifeAdmin\Filament\Resources\ShoppingListResource\RelationManagers;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

/**
 * Stavke liste za kupovinu. `is_done` je ToggleColumn — štiklira se odmah (bez
 * otvaranja forme), niska frikcija (CLAUDE.md §1). Vidljivost nasljeđuje od liste.
 */
class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = null;

    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')
                ->label(__('lifeadmin.items.fields.name'))
                ->required()
                ->maxLength(255),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->heading(__('lifeadmin.items.plural_label'))
            ->columns([
                ToggleColumn::make('is_done')->label(__('lifeadmin.items.fields.is_done')),
                TextColumn::make('name')
                    ->label(__('lifeadmin.items.fields.name'))
                    ->description(fn () => null)
                    ->color(fn ($record) => $record->is_done ? 'gray' : null),
            ])
            ->defaultSort('is_done')
            ->headerActions([
                CreateAction::make()->label(__('lifeadmin.items.actions.add')),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->emptyStateHeading(__('lifeadmin.items.empty.heading'))
            ->emptyStateDescription(__('lifeadmin.items.empty.description'))
            ->emptyStateIcon('heroicon-o-shopping-cart');
    }
}
