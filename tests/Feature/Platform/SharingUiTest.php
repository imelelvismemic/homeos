<?php

use App\Modules\LifeAdmin\Filament\Resources\ContactResource\Pages\ListContacts;
use App\Modules\LifeAdmin\Models\Contact;
use App\Platform\Enums\Visibility;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('app'));
});

it('makes a record private through the shared Share action', function () {
    [$household, $owner, $members] = makeHousehold(extraMembers: 1);
    $other = $members[0];
    test()->actingAs($owner->user);
    Filament::setTenant($household);

    $contact = Contact::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'name' => 'Privatni kontakt',
    ]);

    expect($contact->isVisibleTo($other->user))->toBeTrue(); // household-vidljiv po defaultu

    Livewire::test(ListContacts::class)
        ->callTableAction('share', $contact, data: ['visibility' => Visibility::Private->value]);

    $contact->refresh();
    expect($contact->share->visibility)->toBe(Visibility::Private);
    expect($contact->isVisibleTo($other->user))->toBeFalse();
});

it('shares a record with specific members through the Share action', function () {
    [$household, $owner, $members] = makeHousehold(extraMembers: 2);
    [$a, $b] = $members;
    test()->actingAs($owner->user);
    Filament::setTenant($household);

    $contact = Contact::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'name' => 'Odabrani',
    ]);

    Livewire::test(ListContacts::class)
        ->callTableAction('share', $contact, data: [
            'visibility' => Visibility::Specific->value,
            'share_members' => [$a->id],
        ]);

    $contact->refresh();
    expect($contact->share->visibility)->toBe(Visibility::Specific);
    expect($contact->isVisibleTo($a->user))->toBeTrue();
    expect($contact->isVisibleTo($b->user))->toBeFalse();
});
