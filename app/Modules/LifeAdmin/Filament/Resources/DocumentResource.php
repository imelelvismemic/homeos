<?php

namespace App\Modules\LifeAdmin\Filament\Resources;

use App\Modules\LifeAdmin\Enums\DocumentType;
use App\Modules\LifeAdmin\Filament\Resources\DocumentResource\Pages;
use App\Modules\LifeAdmin\Models\Document;
use App\Platform\Filament\Resources\ModuleResource;
use App\Platform\Filament\Sharing\SharingForm;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class DocumentResource extends ModuleResource
{
    protected static ?string $model = Document::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder';

    protected static ?string $tenantOwnershipRelationshipName = 'household';

    protected static ?int $navigationSort = 1;

    public static function getModelLabel(): string
    {
        return __('lifeadmin.documents.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('lifeadmin.documents.plural_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('lifeadmin.documents.navigation_label');
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
            Select::make('type')
                ->label(__('lifeadmin.documents.fields.type'))
                ->options(DocumentType::options())
                ->default(DocumentType::Other->value)
                ->required(),

            TextInput::make('title')
                ->label(__('lifeadmin.documents.fields.title'))
                ->required()
                ->maxLength(255),

            DatePicker::make('expiry_date')
                ->label(__('lifeadmin.documents.fields.expiry_date'))
                ->helperText(__('lifeadmin.documents.fields.expiry_date_help'))
                ->native(false)
                ->displayFormat('d.m.Y'),

            TextInput::make('remind_days_before')
                ->label(__('lifeadmin.documents.fields.remind_days_before'))
                ->helperText(__('lifeadmin.documents.fields.remind_days_before_help'))
                ->numeric()->minValue(0)->default(30)->required(),

            // Privatni disk 'documents' (nije web-dostupan). Preview/open bi tražili
            // javni URL (local disk ga nema) — zato ugašeni; preuzimanje ide kroz
            // autentikovanu akciju u tabeli (Policy provjera).
            FileUpload::make('file_path')
                ->label(__('lifeadmin.documents.fields.file'))
                ->helperText(__('lifeadmin.documents.fields.file_help'))
                ->disk('documents')
                ->directory('files')
                ->visibility('private')
                ->storeFileNamesIn('file_name')
                ->previewable(false)
                ->openable(false)
                ->downloadable(false)
                ->maxSize(10240)
                ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                ->columnSpanFull(),

            Textarea::make('notes')
                ->label(__('lifeadmin.documents.fields.notes'))
                ->rows(3)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')
                    ->label(__('lifeadmin.documents.fields.type'))
                    ->badge()
                    ->formatStateUsing(fn (DocumentType $state) => $state->label())
                    ->icon(fn (DocumentType $state) => $state->icon()),
                TextColumn::make('title')->label(__('lifeadmin.documents.fields.title'))->searchable()->weight('medium'),
                TextColumn::make('expiry_date')
                    ->label(__('lifeadmin.documents.fields.expiry_date'))
                    ->date('d.m.Y.')
                    ->placeholder('—')
                    ->sortable()
                    ->color(fn (Document $r) => $r->expiry_date && $r->expiry_date->isPast() ? 'danger' : null),
                IconColumn::make('file_path')
                    ->label(__('lifeadmin.documents.fields.file'))
                    ->boolean()
                    ->state(fn (Document $r) => $r->hasFile()),
            ])
            ->defaultSort('expiry_date')
            ->filters([
                SelectFilter::make('type')
                    ->label(__('lifeadmin.documents.fields.type'))
                    ->options(DocumentType::options()),
            ])
            ->actions([
                Action::make('download')
                    ->label(__('lifeadmin.documents.actions.download'))
                    ->icon('heroicon-m-arrow-down-tray')
                    ->visible(fn (Document $r) => $r->hasFile())
                    ->action(fn (Document $r) => Storage::disk('documents')->download($r->file_path, $r->file_name ?? $r->title)),
                EditAction::make(),
                SharingForm::tableAction(),
                DeleteAction::make()
                    ->modalHeading(__('lifeadmin.documents.delete'))
                    ->modalDescription(fn (Document $r) => __('lifeadmin.documents.delete_description', ['title' => $r->title])),
            ])
            ->emptyStateHeading(__('lifeadmin.documents.empty.heading'))
            ->emptyStateDescription(__('lifeadmin.documents.empty.description'))
            ->emptyStateIcon('heroicon-o-folder');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocuments::route('/'),
            'create' => Pages\CreateDocument::route('/create'),
            'edit' => Pages\EditDocument::route('/{record}/edit'),
        ];
    }
}
