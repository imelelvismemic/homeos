<?php

namespace App\Modules\Finance\Search;

use App\Modules\Finance\Filament\Resources\BillResource;
use App\Modules\Finance\Filament\Resources\TransactionResource;
use App\Modules\Finance\Models\Bill;
use App\Modules\Finance\Models\Transaction;
use App\Platform\Contracts\SearchProviderContract;
use App\Platform\Models\Household;
use App\Platform\Search\SearchResult;
use Illuminate\Support\Collection;

class FinanceSearchProvider implements SearchProviderContract
{
    public function search(string $query, Household $household): Collection
    {
        $bills = Bill::query()
            ->where('household_id', $household->id)
            ->visibleTo(auth()->user())
            ->where('title', 'like', "%{$query}%")
            ->limit(5)
            ->get()
            ->map(fn (Bill $bill) => new SearchResult(
                type: 'bill',
                id: $bill->id,
                title: $bill->title,
                url: BillResource::getUrl('edit', ['record' => $bill, 'tenant' => $household]),
                icon: 'heroicon-o-document-currency-euro',
            ));

        $transactions = Transaction::query()
            ->where('household_id', $household->id)
            ->visibleTo(auth()->user())
            ->where('title', 'like', "%{$query}%")
            ->limit(5)
            ->get()
            ->map(fn (Transaction $transaction) => new SearchResult(
                type: 'transaction',
                id: $transaction->id,
                title: $transaction->title,
                url: TransactionResource::getUrl('edit', ['record' => $transaction, 'tenant' => $household]),
                icon: 'heroicon-o-banknotes',
            ));

        return $bills->concat($transactions);
    }

    public function type(): string
    {
        return 'finance';
    }
}
