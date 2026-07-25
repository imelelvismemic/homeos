<?php

use App\Platform\Enums\DigestFrequency;
use App\Platform\Filament\Pages\NotificationSettings;
use App\Platform\Models\NotificationPreference;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('app'));
});

it('saves per-category email preferences and digest frequency for the current member', function () {
    [$household, $owner] = makeHousehold();
    test()->actingAs($owner->user);
    Filament::setTenant($household);

    Livewire::test(NotificationSettings::class)
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
