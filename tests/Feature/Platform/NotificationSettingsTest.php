<?php

use App\Platform\Enums\DigestFrequency;
use App\Platform\Filament\Pages\UserProfile;
use App\Platform\Models\NotificationPreference;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('app'));
});

// Postavke obavještenja su kartica na profilu korisnika, ne zasebna stranica.
it('saves per-category email preferences and digest frequency for the current member', function () {
    [$household, $owner] = makeHousehold();
    test()->actingAs($owner->user);
    Filament::setTenant($household);

    Livewire::test(UserProfile::class)
        ->fillForm([
            'email_bill_due' => true,
            'email_task_assigned' => false,
            'email_shared_with_you' => false,
            'digest_frequency' => DigestFrequency::Weekly->value,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($owner->fresh()->digest_frequency)->toBe(DigestFrequency::Weekly);

    $billPref = NotificationPreference::where('household_member_id', $owner->id)
        ->where('category', 'bill_due')->first();
    $taskPref = NotificationPreference::where('household_member_id', $owner->id)
        ->where('category', 'task_assigned')->first();

    expect($billPref->email_enabled)->toBeTrue();
    expect($taskPref->email_enabled)->toBeFalse();
});

it('keeps the account data untouched when only notification settings change', function () {
    [$household, $owner] = makeHousehold();
    test()->actingAs($owner->user);
    Filament::setTenant($household);

    $name = $owner->user->name;

    Livewire::test(UserProfile::class)
        ->fillForm(['digest_frequency' => DigestFrequency::Daily->value])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($owner->user->fresh()->name)->toBe($name);
    expect($owner->fresh()->digest_frequency)->toBe(DigestFrequency::Daily);
});
