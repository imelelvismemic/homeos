<?php

use App\Modules\Pets\Enums\CareType;
use App\Modules\Pets\Enums\PetSpecies;
use App\Modules\Pets\Events\CareScheduled;
use App\Modules\Pets\Events\PetCreated;
use App\Modules\Pets\Models\CareRecord;
use App\Modules\Pets\Models\Pet;
use App\Modules\Reminders\Models\Reminder;
use App\Platform\Enums\Visibility;
use Illuminate\Support\Facades\Event;

function makePet(array $attributes = []): Pet
{
    [$household, $owner] = $attributes['_ctx'] ?? makeHousehold();
    unset($attributes['_ctx']);

    return Pet::create(array_merge([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'name' => 'Luna',
        'species' => PetSpecies::Dog,
    ], $attributes));
}

it('creates a pet with a household-visible share and dispatches PetCreated', function () {
    Event::fake([PetCreated::class]);

    $pet = makePet();

    expect($pet->exists)->toBeTrue();
    expect($pet->share->visibility)->toBe(Visibility::Household);
    Event::assertDispatched(PetCreated::class, fn ($e) => $e->pet->is($pet));
});

it('dispatches CareScheduled when care is recorded', function () {
    $ctx = makeHousehold();
    $pet = makePet(['_ctx' => $ctx]);
    Event::fake([CareScheduled::class]);

    $care = CareRecord::create([
        'household_id' => $pet->household_id,
        'created_by' => $pet->created_by,
        'pet_id' => $pet->id,
        'type' => CareType::Vaccination,
        'due_date' => now()->addDays(10),
    ]);

    Event::assertDispatched(CareScheduled::class, fn ($e) => $e->careRecord->is($care));
});

it('proves extensibility: care with a date creates a reminder without touching Reminders', function () {
    $ctx = makeHousehold();
    $pet = makePet(['_ctx' => $ctx, 'name' => 'Tara']);

    CareRecord::create([
        'household_id' => $pet->household_id,
        'created_by' => $pet->created_by,
        'pet_id' => $pet->id,
        'type' => CareType::Vaccination,
        'due_date' => now()->addDays(10),
        'remind_days_before' => 3,
    ]);

    $reminder = Reminder::query()->latest('id')->first();

    expect($reminder)->not->toBeNull();
    expect($reminder->title)->toContain('Tara');
    expect($reminder->due_date->toDateString())->toBe(now()->addDays(7)->toDateString());
    expect($reminder->remindable)->toBeInstanceOf(CareRecord::class);
});

it('keeps a private pet hidden from other members of the same household', function () {
    [$household, $owner, $members] = makeHousehold(extraMembers: 1);
    $other = $members[0]->user;

    $pet = makePet(['_ctx' => [$household, $owner], 'name' => 'Tajni hrčak']);
    $pet->makePrivate();

    expect($pet->fresh()->isVisibleTo($other))->toBeFalse();
    expect($pet->fresh()->isVisibleTo($owner->user))->toBeTrue();
});

it('never shows a pet to someone outside the household', function () {
    $pet = makePet();
    [, $strangerMember] = makeHousehold();

    // Napomena: `scopeVisibleTo` filtrira vidljivost UNUTAR domaćinstva —
    // izolaciju domaćinstava radi Filament tenancy (vidi Shareable docblock).
    // Zato se članstvo provjerava kroz isVisibleTo, a kroz Resource je pokriveno
    // u PetResourceTest.
    expect($pet->isVisibleTo($strangerMember->user))->toBeFalse();
    expect($pet->isVisibleTo($pet->creator))->toBeTrue();
});
