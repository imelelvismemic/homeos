<?php

namespace App\Modules\Finance\Listeners;

use App\Modules\Finance\Events\BillPaid;
use App\Modules\Finance\Models\Bill;
use App\Platform\Recurrence\RecurrenceService;

/**
 * Auto-discoveran listener: kad se ponavljajući račun plati, kreira sljedeću
 * instancu s pomjerenim dospijećem. Njeno kreiranje (BillCreated) ponovo
 * dispatch-uje podsjetnik (RequestBillReminder) — bez dodatnog koda.
 */
class SpawnRecurringBill
{
    public function __construct(private RecurrenceService $recurrence) {}

    public function handle(BillPaid $event): void
    {
        $bill = $event->bill;

        if (! $bill->isRecurring()) {
            return;
        }

        $next = $this->recurrence->nextDueDate($bill->recurrence_rule, $bill->due_date);

        if ($next === null) {
            return;
        }

        Bill::create([
            'household_id' => $bill->household_id,
            'created_by' => $bill->created_by,
            'category_id' => $bill->category_id,
            'title' => $bill->title,
            'amount' => $bill->amount,
            'due_date' => $next,
            'recurrence_rule' => $bill->recurrence_rule,
            'remind_days_before' => $bill->remind_days_before,
        ]);
    }
}
