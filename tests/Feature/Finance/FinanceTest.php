<?php

use App\Modules\Finance\Enums\TransactionType;
use App\Modules\Finance\Events\TransactionCreated;
use App\Modules\Finance\Models\Bill;
use App\Modules\Finance\Models\Category;
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

it('records a linked expense transaction when a bill is marked paid (sve je povezano)', function () {
    [$household, $owner] = makeHousehold();
    $category = Category::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'name' => 'Režije',
    ]);

    $bill = Bill::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'category_id' => $category->id,
        'title' => 'Struja',
        'amount' => 87.30,
        'due_date' => now()->addDays(5)->toDateString(),
        'remind_days_before' => 3,
    ]);

    $bill->update(['paid_at' => now()]);

    $transaction = Transaction::query()->where('bill_id', $bill->id)->first();

    expect($transaction)->not->toBeNull();
    expect($transaction->type)->toBe(TransactionType::Expense);
    expect((float) $transaction->amount)->toBe(87.30);
    expect($transaction->category_id)->toBe($category->id);
    expect($transaction->title)->toBe('Struja');
    expect($transaction->paid_by)->toBeNull();
});

it('does not create a duplicate expense when a bill payment is re-triggered (idempotent)', function () {
    [$household, $owner] = makeHousehold();

    $bill = Bill::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'title' => 'Voda',
        'amount' => 30.00,
        'due_date' => now()->addDays(5)->toDateString(),
        'remind_days_before' => 3,
    ]);

    $bill->update(['paid_at' => now()]);
    $bill->update(['paid_at' => null]);
    $bill->update(['paid_at' => now()]);

    expect(Transaction::query()->where('bill_id', $bill->id)->count())->toBe(1);
});

it('mirrors a private bill onto its derived expense transaction (privacy)', function () {
    [$household, $owner] = makeHousehold();

    $bill = Bill::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'title' => 'Privatni račun',
        'amount' => 50.00,
        'due_date' => now()->addDays(5)->toDateString(),
        'remind_days_before' => 3,
    ]);
    $bill->makePrivate();

    $bill->update(['paid_at' => now()]);

    $transaction = Transaction::query()->where('bill_id', $bill->id)->first();

    expect($transaction)->not->toBeNull();
    expect($transaction->share->visibility)->toBe(Visibility::Private);
});
