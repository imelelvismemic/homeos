<?php

namespace App\Modules\LifeAdmin\Filament\Resources;

use App\Modules\LifeAdmin\Filament\Resources\ContactResource\Pages;
use App\Modules\LifeAdmin\Models\Contact;
use App\Platform\Filament\Sharing\SharingForm;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ContactResource extends Resource
{
    protected static ?string $model = Contact::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $tenantOwnershipRelationshipName = 'household';

    protected static ?int $navigationSort = 2;

    public static function getModelLabel(): string
    {
        return __('lifeadmin.contacts.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('lifeadmin.contacts.plural_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('lifeadmin.contacts.navigation_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('lifeadmin.navigation_group');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->visibleTo(auth()->user());
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')
                ->label(__('lifeadmin.contacts.fields.name'))
                ->required()
                ->maxLength(255),

            TextInput::make('relationship')
                ->label(__('lifeadmin.contacts.fields.relationship'))
                ->helperText(__('lifeadmin.contacts.fields.relationship_help'))
                ->maxLength(255),

            TextInput::make('phone')
                ->label(__('lifeadmin.contacts.fields.phone'))
                ->tel()
                ->maxLength(255),

            TextInput::make('email')
                ->label(__('lifeadmin.contacts.fields.email'))
                ->email()
                ->maxLength(255),

            Textarea::make('notes')
                ->label(__('lifeadmin.contacts.fields.notes'))
                ->rows(3)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label(__('lifeadmin.contacts.fields.name'))->searchable()->weight('medium'),
                TextColumn::make('relationship')->label(__('lifeadmin.contacts.fields.relationship'))->placeholder('—')->searchable(),
                TextColumn::make('phone')->label(__('lifeadmin.contacts.fields.phone'))->placeholder('—')->searchable()->copyable(),
                TextColumn::make('email')->label(__('lifeadmin.contacts.fields.email'))->placeholder('—')->searchable()->copyable()->toggleable(),
            ])
            ->defaultSort('name')
            ->actions([
                EditAction::make(),
                SharingForm::tableAction(),
                DeleteAction::make()
                    ->modalHeading(__('lifeadmin.contacts.delete'))
                    ->modalDescription(fn (Contact $r) => __('lifeadmin.contacts.delete_description', ['name' => $r->name])),
            ])
            ->emptyStateHeading(__('lifeadmin.contacts.empty.heading'))
            ->emptyStateDescription(__('lifeadmin.contacts.empty.description'))
            ->emptyStateIcon('heroicon-o-user-circle');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContacts::route('/'),
            'create' => Pages\CreateContact::route('/create'),
            'edit' => Pages\EditContact::route('/{record}/edit'),
        ];
    }
}
