<?php

namespace App\Modules\Finance\Digest;

use App\Models\User;
use App\Modules\Finance\Models\Bill;
use App\Modules\Finance\Support\Money;
use App\Platform\Contracts\DigestSourceContract;
use App\Platform\Digest\DigestSection;
use App\Platform\Models\Household;
use Carbon\CarbonInterface;

/**
 * Doprinos Finansija digestu (Faza 6): neplaćeni računi koji dospijevaju u periodu,
 * vidljivi članu.
 */
class BillDigestSource implements DigestSourceContract
{
    public function digestSection(Household $household, User $user, CarbonInterface $from, CarbonInterface $to): ?DigestSection
    {
        $bills = Bill::query()
            ->where('household_id', $household->id)
            ->visibleTo($user)
            ->whereNull('paid_at')
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$from, $to])
            ->orderBy('due_date')
            ->get();

        if ($bills->isEmpty()) {
            return null;
        }

        return new DigestSection(
            __('finance.bills.plural_label'),
            $bills->map(fn (Bill $b) => '• '.$b->title.' ('.Money::format($b->amount).') — '.$b->due_date->translatedFormat('d.m.'))->all(),
        );
    }
}
