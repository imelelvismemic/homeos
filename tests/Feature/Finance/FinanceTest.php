<?php

use App\Modules\Finance\Enums\TransactionType;
use App\Modules\Finance\Events\TransactionCreated;
use App\Modules\Finance\Models\Bill;
use App\Modules\Finance\Models\Transaction;
use App\Modules\Reminders\Models\Reminder;
use App\Platform\Enums\Visibility;
use Illuminate\Support\Facades\Event;

it('creates a transaction with a household-visible share and dispatches TransactionCreated', function () {
    [$household, $owner] = makeHousehold();
    Event::fake([TransactionCreated::class]);

    $transaction = Transaction::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'type' => TransactionType::Expense,
        'title' => 'Kupovina',
        'amount' => 42.50,
        'date' => now()->toDateString(),
    ]);

    expect($transaction->exists)->toBeTrue();
    expect($transaction->share->visibility)->toBe(Visibility::Household);
    Event::assertDispatched(TransactionCreated::class, fn ($e) => $e->transaction->is($transaction));
});

it('automatically requests a reminder when a bill is created (DoD, no code outside Finance)', function () {
    [$household, $owner] = makeHousehold();

    $bill = Bill::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'title' => 'Struja',
        'amount' => 120.00,
        'due_date' => now()->addDays(10)->toDateString(),
        'remind_days_before' => 3,
    ]);

    // BillCreated → RequestBillReminder → ReminderRequested → CreateRequestedReminder → Reminder
    $reminder = Reminder::query()
        ->where('remindable_type', $bill->getMorphClass())
        ->where('remindable_id', $bill->id)
        ->first();

    expect($reminder)->not->toBeNull();
    expect($reminder->household_id)->toBe($household->id);
    expect($reminder->due_date->toDateString())->toBe(now()->addDays(7)->toDateString()); // 10 − 3
    expect($reminder->remindable->is($bill))->toBeTrue();
});

it('spawns the next instance when a recurring bill is marked paid', function () {
    [$household, $owner] = makeHousehold();

    $bill = Bill::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'title' => 'Netflix',
        'amount' => 20.00,
        'due_date' => now()->startOfDay()->toDateString(),
        'recurrence_rule' => 'FREQ=MONTHLY',
        'remind_days_before' => 2,
    ]);

    $bill->update(['paid_at' => now()]);

    $next = Bill::query()->whereKeyNot($bill->id)->latest('id')->first();

    expect($next)->not->toBeNull();
    expect($next->isPaid())->toBeFalse();
    expect($next->due_date->toDateString())->toBe($bill->due_date->copy()->addMonthNoOverflow()->toDateString());
});
