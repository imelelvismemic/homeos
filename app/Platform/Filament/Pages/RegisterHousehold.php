<?php

namespace App\Platform\Filament\Pages;

use App\Platform\Models\Household;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Tenancy\RegisterTenant;
use Illuminate\Database\Eloquent\Model;

class RegisterHousehold extends RegisterTenant
{
    public static function getLabel(): string
    {
        return __('platform.household.register_heading');
    }

    /**
     * Kreiranje domaćinstva nije stalna opcija: stranicu vidi samo korisnik koji
     * još nije ni u jednom domaćinstvu. Time nestaje i stavka iz padajućeg menija
     * domaćinstva (Filament je krije po `canView`), a Filamentovo preusmjeravanje
     * nakon prijave i dalje radi — korisnik koji se registrovao pa zatvorio browser
     * prije nego je dovršio kreiranje, dobije formu odmah po prijavi.
     *
     * Namjerno "nije ni u jednom domaćinstvu", ne "nije vlasnik nijednog": pozvani
     * član nema svoje domaćinstvo, ali ima gdje raditi — ne smijemo ga svaki put
     * tjerati da kreira vlastito.
     */
    public static function canView(): bool
    {
        return auth()->user()?->households()->doesntExist() ?? false;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label(__('platform.household.name'))
                    ->placeholder(__('platform.household.name_placeholder'))
                    ->required()
                    ->maxLength(255),
            ]);
    }

    protected function handleRegistration(array $data): Model
    {
        $household = Household::create([
            'name' => $data['name'],
            'owner_id' => auth()->id(),
        ]);

        $household->members()->create([
            'user_id' => auth()->id(),
            'role' => 'owner',
            'joined_at' => now(),
        ]);

        auth()->user()->update(['current_household_id' => $household->id]);

        return $household;
    }
}
