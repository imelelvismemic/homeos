<?php

namespace App\Modules\Finance\Listeners;

use App\Modules\Finance\Enums\TransactionType;
use App\Modules\Finance\Events\BillPaid;
use App\Modules\Finance\Models\Transaction;
use App\Platform\Sharing\VisibilityMirror;

/**
 * Kad se račun označi plaćenim, automatski se bilježi trošak (DATA_MODEL.md §4b,
 * princip "sve je povezano" + "ne dupliraj"): plaćeni račun je stvarni rashod pa
 * se pojavljuje u mjesečnom pregledu/kategorijama kao i svaki drugi trošak. Trošak
 * je vezan za račun (`bill_id`) — to daje provenance i idempotenciju (jedan trošak
 * po plaćenom računu, čak i ako se paid_at ponovo prebaci). Bill i Transaction su
 * isti modul, pa nema kršenja granica modula.
 *
 * Trošak nastao računom nema platioca/učesnike (`paid_by` = null) — to je obaveza
 * domaćinstva, ne ulazi u "ko duguje kome" saldo dok ga korisnik ručno ne podijeli.
 */
class RecordBillPayment
{
    public function handle(BillPaid $event): void
    {
        $bill = $event->bill;

        // Idempotencija: ako trošak za ovaj račun već postoji, ne dupliraj.
        if (Transaction::query()->where('bill_id', $bill->id)->exists()) {
            return;
        }

        $transaction = Transaction::create([
            'household_id' => $bill->household_id,
            'created_by' => $bill->created_by,
            'category_id' => $bill->category_id,
            'type' => TransactionType::Expense->value,
            'title' => $bill->title,
            'amount' => $bill->amount,
            'date' => $bill->paid_at?->toDateString() ?? now()->toDateString(),
            'bill_id' => $bill->id,
        ]);

        // Trošak nasljeđuje privatnost računa (privatan račun → privatan trošak).
        VisibilityMirror::mirror($bill, $transaction);
    }
}
