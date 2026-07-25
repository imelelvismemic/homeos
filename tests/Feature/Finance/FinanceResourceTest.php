<?php

use App\Modules\Finance\Enums\TransactionType;
use App\Modules\Finance\Filament\Resources\BillResource\Pages\CreateBill;
use App\Modules\Finance\Filament\Resources\BillResource\Pages\ListBills;
use App\Modules\Finance\Filament\Resources\TransactionResource\Pages\CreateTransaction;
use App\Modules\Finance\Models\Bill;
use App\Modules\Finance\Models\Transaction;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('app'));
});

it('creates a bill through the resource, stamping household and creator', function () {
    [$household, $owner] = makeHousehold();
    test()->actingAs($owner->user);
    Filament::setTenant($household);

    Livewire::test(CreateBill::class)
        ->fillForm([
            'title' => 'Voda',
            'amount' => 18.90,
            'due_date' => now()->addDays(6)->toDateString(),
            'remind_days_before' => 3,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $bill = Bill::firstWhere('title', 'Voda');

    expect($bill)->not->toBeNull();
    expect($bill->household_id)->toBe($household->id);
    expect($bill->created_by)->toBe($owner->user_id);
});

it('creates a transaction with split participants', function () {
    [$household, $owner, $members] = makeHousehold(extraMembers: 1);
    test()->actingAs($owner->user);
    Filament::setTenant($household);
    $other = $members[0];

    Livewire::test(CreateTransaction::class)
        ->fillForm([
            'type' => TransactionType::Expense->value,
            'title' => 'Ručak',
            'amount' => 40,
            'date' => now()->toDateString(),
            'paid_by' => $owner->id,
            'participants' => [$owner->id, $other->id],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $transaction = Transaction::firstWhere('title', 'Ručak');

    expect($transaction)->not->toBeNull();
    expect($transaction->participants()->count())->toBe(2);
});

it('never shows a bill to a member of another household', function () {
    [$householdA, $ownerA] = makeHousehold();
    [$householdB, $ownerB] = makeHousehold();

    $bill = Bill::create([
        'household_id' => $householdA->id,
        'created_by' => $ownerA->user_id,
        'title' => 'Tajni račun A',
        'amount' => 50,
        'due_date' => now()->addDay()->toDateString(),
    ]);

    test()->actingAs($ownerB->user);
    Filament::setTenant($householdB);

    Livewire::test(ListBills::class)->assertCanNotSeeTableRecords([$bill]);
});
