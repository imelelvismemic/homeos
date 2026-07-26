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
     * Kreiranje domaćinstva nije stalna opcija, ali mora ostati dostupno onome ko
     * još nema SVOJE domaćinstvo — uključujući pozvanog člana (DATA_MODEL.md §1:
     * odrasla djeca imaju svoje domaćinstvo, a i dalje su u roditeljskom).
     *
     * Zato je uslov "nije vlasnik nijednog domaćinstva", a ne "nije ni u jednom":
     *  - potpuno nov korisnik → vidi formu, i Filament ga na nju preusmjeri nakon
     *    prijave (nema nijedno domaćinstvo), čime je pokriven slučaj "registrovao
     *    se pa zatvorio browser prije nego je dovršio kreiranje";
     *  - pozvani član → forma mu je dostupna iz menija domaćinstva, ali ga niko na
     *    nju ne tjera (ima gdje raditi);
     *  - ko već ima svoje domaćinstvo → stavke nema, ni URL ne radi.
     *
     * Nametanje forme je odvojeno pitanje: njega radi Filament i dešava se samo
     * kad korisnik nema NIJEDNO domaćinstvo.
     */
    public static function canView(): bool
    {
        return auth()->user()?->ownedHouseholds()->doesntExist() ?? false;
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
