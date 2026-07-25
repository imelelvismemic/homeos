<?php

namespace App\Platform\Filament\Sharing;

use App\Platform\Enums\Visibility;
use App\Platform\Models\HouseholdMember;
use Filament\Actions\Action as PageAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Select;
use Filament\Forms\Get;
use Filament\Tables\Actions\Action as TableAction;
use Illuminate\Database\Eloquent\Model;

/**
 * Zajednički, modul-neutralni UI za privatnost/dijeljenje (CLAUDE.md §11, Faza 6).
 * Radi za BILO KOJI Shareable objekat — modul samo zakači akciju (tablica/edit) i,
 * po želji, polja u create formu. Vidljivost se čuva u `shares` tabeli (Shareable
 * trait), ne na modelu — zato su polja transientna (`dehydrated(false)`).
 */
class SharingForm
{
    /**
     * Filament form komponente — koriste se i u create formi i u modalu akcije.
     *
     * @return array<int, Component>
     */
    public static function components(): array
    {
        return [
            Select::make('visibility')
                ->label(__('platform.sharing.visibility'))
                ->options(collect(Visibility::cases())
                    ->mapWithKeys(fn (Visibility $v) => [$v->value => $v->label()])
                    ->all())
                ->default(Visibility::Household->value)
                ->live()
                ->required(),

            Select::make('share_members')
                ->label(__('platform.sharing.members'))
                ->helperText(__('platform.sharing.members_help'))
                ->multiple()
                ->options(fn () => static::memberOptions())
                ->visible(fn (Get $get) => $get('visibility') === Visibility::Specific->value),
        ];
    }

    /** @return array<int|string, string> */
    protected static function memberOptions(): array
    {
        $household = Filament::getTenant();

        if ($household === null) {
            return [];
        }

        return $household->members()->with('user')->get()
            ->mapWithKeys(fn (HouseholdMember $m) => [$m->id => $m->user?->name ?? '—'])
            ->all();
    }

    /**
     * Trenutno stanje vidljivosti (za popunjavanje edit/action modala).
     *
     * @return array<string, mixed>
     */
    public static function fill(Model $record): array
    {
        $share = $record->share;
        $visibility = $share?->visibility ?? Visibility::Household;

        return [
            'visibility' => $visibility instanceof Visibility ? $visibility->value : $visibility,
            'share_members' => $share !== null && $visibility === Visibility::Specific
                ? $share->recipients()->pluck('household_member_id')->all()
                : [],
        ];
    }

    /**
     * Primijeni izbor vidljivosti na Shareable objekat.
     *
     * @param  array<string, mixed>  $data
     */
    public static function apply(Model $record, array $data): void
    {
        $visibility = $data['visibility'] ?? Visibility::Household->value;

        match ($visibility) {
            Visibility::Private->value => $record->makePrivate(),
            Visibility::Specific->value => $record->shareWith($data['share_members'] ?? []),
            default => $record->shareWithHousehold(),
        };
    }

    /** Akcija za red tablice (lista) — brza promjena vidljivosti. */
    public static function tableAction(): TableAction
    {
        return TableAction::make('share')
            ->label(__('platform.sharing.action'))
            ->icon('heroicon-m-user-group')
            ->modalHeading(__('platform.sharing.modal_heading'))
            ->modalSubmitActionLabel(__('platform.sharing.submit'))
            ->fillForm(fn (Model $record) => static::fill($record))
            ->form(static::components())
            ->action(fn (Model $record, array $data) => static::apply($record, $data));
    }

    /** Akcija u zaglavlju edit stranice — ista logika kao tablica. */
    public static function pageAction(): PageAction
    {
        return PageAction::make('share')
            ->label(__('platform.sharing.action'))
            ->icon('heroicon-m-user-group')
            ->modalHeading(__('platform.sharing.modal_heading'))
            ->modalSubmitActionLabel(__('platform.sharing.submit'))
            ->fillForm(fn (Model $record) => static::fill($record))
            ->form(static::components())
            ->action(fn (Model $record, array $data) => static::apply($record, $data));
    }
}
