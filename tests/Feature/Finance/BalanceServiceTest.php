<?php

use App\Modules\Finance\Enums\TransactionType;
use App\Modules\Finance\Models\Transaction;
use App\Modules\Finance\Services\BalanceService;

it('computes net balances from equally split expenses (who owes whom)', function () {
    [$household, $owner, $members] = makeHousehold(extraMembers: 1);
    $other = $members[0];

    // Vlasnik platio 100, podijeljeno na oboje → svako duguje 50.
    // Vlasnik: platio 100 − udio 50 = +50 (drugi mu duguje). Drugi: −50 (duguje).
    $expense = Transaction::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'type' => TransactionType::Expense,
        'title' => 'Zajednička kupovina',
        'amount' => 100.00,
        'date' => now()->toDateString(),
        'paid_by' => $owner->id,
    ]);
    $expense->participants()->sync([$owner->id, $other->id]);

    $balances = app(BalanceService::class)->balances($household);

    expect($balances[$owner->id])->toBe(50.0);
    expect($balances[$other->id])->toBe(-50.0);
});

it('ignores expenses without participants and income', function () {
    [$household, $owner] = makeHousehold();

    Transaction::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'type' => TransactionType::Expense,
        'title' => 'Bez podjele',
        'amount' => 30.00,
        'date' => now()->toDateString(),
        'paid_by' => $owner->id,
    ]); // nema participants

    Transaction::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'type' => TransactionType::Income,
        'title' => 'Plata',
        'amount' => 1000.00,
        'date' => now()->toDateString(),
        'paid_by' => $owner->id,
    ]);

    expect(app(BalanceService::class)->balances($household))->toBeEmpty();
});
