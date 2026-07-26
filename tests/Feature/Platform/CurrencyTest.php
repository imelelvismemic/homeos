<?php

use App\Modules\Finance\Support\Money;
use App\Platform\Filament\Tenancy\EditHouseholdProfile;
use App\Platform\Modules\ModuleRegistry;
use App\Platform\Support\Currency;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('app'));
});

it('formats amounts in the currency chosen by the household', function () {
    [$household, $owner] = makeHousehold();
    test()->actingAs($owner->user);
    Filament::setTenant($household);

    $household->update(['currency' => 'EUR']);
    expect(Money::format(1234.5))->toBe('1,234.50 €');

    $household->update(['currency' => 'BAM']);
    Filament::setTenant($household->fresh());
    expect(Money::format(700))->toBe('700.00 KM');
    expect(Currency::symbol())->toBe('KM');
});

it('falls back to the default currency for an unknown or missing code', function () {
    expect(Currency::code('XYZ'))->toBe(Currency::DEFAULT);
    expect(Currency::code(null))->toBe(Currency::DEFAULT);
    expect(Currency::symbol('XYZ'))->toBe('€');
});

it('lets the owner pick the currency in household settings', function () {
    [$household, $owner] = makeHousehold();
    test()->actingAs($owner->user);
    Filament::setTenant($household);

    Livewire::test(EditHouseholdProfile::class, ['tenant' => $household])
        ->assertFormFieldExists('currency')
        ->fillForm(['currency' => 'CHF'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($household->fresh()->currency)->toBe('CHF');
});

it('hides the currency setting when no enabled module uses amounts', function () {
    [$household, $owner] = makeHousehold();
    test()->actingAs($owner->user);
    Filament::setTenant($household);

    // Finansije su jedini modul s `uses_currency` — bez njih polje nema svrhu.
    app(ModuleRegistry::class)->setEnabled($household, 'finance', false);

    Livewire::test(EditHouseholdProfile::class, ['tenant' => $household])
        ->assertFormFieldIsHidden('currency');
});
