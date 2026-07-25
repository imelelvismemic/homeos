<?php

namespace App\Modules\Notes\Filament\Resources\NoteResource\Pages;

use App\Modules\Notes\Filament\Resources\NoteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListNotes extends ListRecords
{
    protected static string $resource = NoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label(__('notes.actions.create')),
            // Dnevnik je "prostor za dnevne bilješke" iz specifikacije — unos za
            // današnji dan se otvara jednim klikom, s već postavljenim datumom.
            CreateAction::make('createJournal')
                ->label(__('notes.actions.create_journal'))
                ->icon('heroicon-o-book-open')
                ->color('gray')
                ->url(fn (): string => NoteResource::getUrl('create', ['journal' => 1])),
        ];
    }

    /**
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('notes.tabs.all')),
            'journal' => Tab::make(__('notes.tabs.journal'))
                ->icon('heroicon-m-book-open')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNotNull('journal_date'))
                ->badge(fn (): int => static::getResource()::getEloquentQuery()->whereNotNull('journal_date')->count()),
        ];
    }
}
