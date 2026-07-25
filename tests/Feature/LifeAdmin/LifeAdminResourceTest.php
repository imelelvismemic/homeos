<?php

use App\Modules\LifeAdmin\Enums\DocumentType;
use App\Modules\LifeAdmin\Filament\Resources\ContactResource\Pages\CreateContact;
use App\Modules\LifeAdmin\Filament\Resources\DocumentResource\Pages\CreateDocument;
use App\Modules\LifeAdmin\Filament\Resources\DocumentResource\Pages\ListDocuments;
use App\Modules\LifeAdmin\Filament\Resources\ShoppingListResource\Pages\CreateShoppingList;
use App\Modules\LifeAdmin\Models\Contact;
use App\Modules\LifeAdmin\Models\Document;
use App\Modules\LifeAdmin\Models\ShoppingItem;
use App\Modules\LifeAdmin\Models\ShoppingList;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('app'));
});

it('creates a document through the resource, stamping household and creator', function () {
    [$household, $owner] = makeHousehold();
    test()->actingAs($owner->user);
    Filament::setTenant($household);

    Livewire::test(CreateDocument::class)
        ->fillForm([
            'type' => DocumentType::Warranty->value,
            'title' => 'Garancija frižider',
            'expiry_date' => now()->addYear()->toDateString(),
            'remind_days_before' => 30,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $document = Document::firstWhere('title', 'Garancija frižider');

    expect($document)->not->toBeNull();
    expect($document->household_id)->toBe($household->id);
    expect($document->created_by)->toBe($owner->user_id);
    expect($document->type)->toBe(DocumentType::Warranty);
});

it('stores an uploaded attachment on the private documents disk', function () {
    Storage::fake('documents');
    [$household, $owner] = makeHousehold();
    test()->actingAs($owner->user);
    Filament::setTenant($household);

    Livewire::test(CreateDocument::class)
        ->fillForm([
            'type' => DocumentType::IdDocument->value,
            'title' => 'Pasoš',
            'remind_days_before' => 30,
            'file_path' => UploadedFile::fake()->create('pasos.pdf', 200, 'application/pdf'),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $document = Document::firstWhere('title', 'Pasoš');

    expect($document->file_path)->not->toBeNull();
    expect($document->file_name)->toBe('pasos.pdf');
    Storage::disk('documents')->assertExists($document->file_path);
});

it('creates a contact through the resource, stamping household and creator', function () {
    [$household, $owner] = makeHousehold();
    test()->actingAs($owner->user);
    Filament::setTenant($household);

    Livewire::test(CreateContact::class)
        ->fillForm([
            'name' => 'Dr. Kovač',
            'relationship' => 'ljekar',
            'phone' => '033111222',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $contact = Contact::firstWhere('name', 'Dr. Kovač');

    expect($contact)->not->toBeNull();
    expect($contact->household_id)->toBe($household->id);
    expect($contact->created_by)->toBe($owner->user_id);
});

it('creates a shopping list with items that can be checked off', function () {
    [$household, $owner] = makeHousehold();
    test()->actingAs($owner->user);
    Filament::setTenant($household);

    Livewire::test(CreateShoppingList::class)
        ->fillForm(['name' => 'Sedmična kupovina'])
        ->call('create')
        ->assertHasNoFormErrors();

    $list = ShoppingList::firstWhere('name', 'Sedmična kupovina');
    expect($list)->not->toBeNull();
    expect($list->household_id)->toBe($household->id);

    $item = ShoppingItem::create(['list_id' => $list->id, 'name' => 'Mlijeko']);
    expect($item->is_done)->toBeFalse();

    $item->update(['is_done' => true]);
    expect($item->fresh()->is_done)->toBeTrue();
});

it('never shows a document to a member of another household', function () {
    [$householdA, $ownerA] = makeHousehold();
    [$householdB, $ownerB] = makeHousehold();

    $document = Document::create([
        'household_id' => $householdA->id,
        'created_by' => $ownerA->user_id,
        'type' => DocumentType::Contract,
        'title' => 'Tajni ugovor A',
        'expiry_date' => now()->addYear()->toDateString(),
    ]);

    test()->actingAs($ownerB->user);
    Filament::setTenant($householdB);

    Livewire::test(ListDocuments::class)->assertCanNotSeeTableRecords([$document]);
});
