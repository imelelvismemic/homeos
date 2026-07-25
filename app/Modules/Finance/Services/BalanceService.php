<?php

namespace App\Modules\Finance\Services;

use App\Modules\Finance\Enums\TransactionType;
use App\Modules\Finance\Models\Transaction;
use App\Platform\Models\Household;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * "Ko je platio, ko duguje" (ORIGINAL_SPEC). Za svaki trošak (expense) s
 * učesnicima: platilac je platio pun iznos, svaki učesnik duguje jednak udio.
 * Net saldo po članu = (platio) − (njegov udio). Pozitivno = drugi duguju njemu,
 * negativno = on duguje. Prihod se ne dijeli.
 */
class BalanceService
{
    /**
     * @return Collection<int, float> household_member_id → net saldo (KM)
     */
    public function balances(Household $household, ?CarbonInterface $from = null, ?CarbonInterface $to = null): Collection
    {
        $query = Transaction::query()
            ->where('household_id', $household->id)
            ->where('type', TransactionType::Expense->value)
            ->whereNotNull('paid_by')
            ->with('participants:id');

        if ($from !== null && $to !== null) {
            $query->whereBetween('date', [$from, $to]);
        }

        $net = [];

        foreach ($query->get() as $transaction) {
            $participants = $transaction->participants;
            $count = $participants->count();

            if ($count === 0) {
                continue; // bez podjele — ne ulazi u "ko duguje"
            }

            $amount = (float) $transaction->amount;
            $share = $amount / $count;

            $net[$transaction->paid_by] = ($net[$transaction->paid_by] ?? 0) + $amount;

            foreach ($participants as $participant) {
                $net[$participant->id] = ($net[$participant->id] ?? 0) - $share;
            }
        }

        return collect($net)->map(fn (float $v) => round($v, 2));
    }
}
