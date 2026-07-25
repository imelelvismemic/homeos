<?php

namespace App\Platform\Filament\Tenancy;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Tenancy\EditTenantProfile;

/**
 * Izmjena naziva domaćinstva. Filament je otvara iz menija za izbor domaćinstva
 * i sam provjerava pristup kroz `canView()` → Policy `update` na Householdu,
 * a taj dozvoljava samo vlasniku (CLAUDE.md §11 — autorizacija ide kroz Policy,
 * ne ručnim `if`-ovima).
 */
class EditHouseholdProfile extends EditTenantProfile
{
    public static function getLabel(): string
    {
        return __('platform.household.settings_label');
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')
                ->label(__('platform.household.name'))
                ->placeholder(__('platform.household.name_placeholder'))
                ->required()
                ->maxLength(255),
        ]);
    }
}
