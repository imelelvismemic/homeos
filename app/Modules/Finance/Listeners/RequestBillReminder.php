<?php

namespace App\Modules\Finance\Listeners;

use App\Modules\Finance\Events\BillCreated;
use App\Platform\Events\ReminderRequested;

/**
 * DoD Faze 5: kad se kreira račun s dospijećem, Finance TRAŽI podsjetnik preko
 * platform eventa (X dana prije dospijeća). Reminders ga kreira, scheduler okine
 * → reminder_fired email. Ništa van modula Finansije — nema importa Reminders.
 */
class RequestBillReminder
{
    public function handle(BillCreated $event): void
    {
        $bill = $event->bill;

        ReminderRequested::dispatch(
            $bill,
            $bill->reminderDate(),
            __('finance.reminder.bill_due', [
                'title' => $bill->title,
                'amount' => number_format((float) $bill->amount, 2, ',', '.'),
            ]),
        );
    }
}
