<?php

namespace App\Modules\Finance\Search;

use App\Modules\Finance\Filament\Resources\BillResource;
use App\Modules\Finance\Filament\Resources\BudgetResource;
use App\Modules\Finance\Filament\Resources\CategoryResource;
use App\Modules\Finance\Filament\Resources\TransactionResource;
use App\Modules\Finance\Models\Bill;
use App\Modules\Finance\Models\Budget;
use App\Modules\Finance\Models\Category;
use App\Modules\Finance\Models\Transaction;
use App\Platform\Contracts\SearchProviderContract;
use App\Platform\Models\Household;
use App\Platform\Search\SearchResult;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class FinanceSearchProvider implements SearchProviderContract
{
    public function search(string $query, Household $household): Collection
    {
        $bills = Bill::query()
            ->where('household_id', $household->id)
            ->visibleTo(auth()->user())
            ->where(fn (Builder $q) => $q->where('title', 'like', "%{$query}%")
                ->orWhereHas('category', fn (Builder $c) => $c->where('name', 'like', "%{$query}%")))
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
            ->where(fn (Builder $q) => $q->where('title', 'like', "%{$query}%")
                ->orWhereHas('category', fn (Builder $c) => $c->where('name', 'like', "%{$query}%")))
            ->limit(5)
            ->get()
            ->map(fn (Transaction $transaction) => new SearchResult(
                type: 'transaction',
                id: $transaction->id,
                title: $transaction->title,
                url: TransactionResource::getUrl('edit', ['record' => $transaction, 'tenant' => $household]),
                icon: 'heroicon-o-banknotes',
            ));

        $categories = Category::query()
            ->where('household_id', $household->id)
            ->where('name', 'like', "%{$query}%")
            ->limit(5)
            ->get()
            ->map(fn (Category $category) => new SearchResult(
                type: 'category',
                id: $category->id,
                title: $category->name,
                url: CategoryResource::getUrl('edit', ['record' => $category, 'tenant' => $household]),
                icon: 'heroicon-o-tag',
            ));

        $budgets = Budget::query()
            ->where('household_id', $household->id)
            ->whereHas('category', fn (Builder $c) => $c->where('name', 'like', "%{$query}%"))
            ->with('category')
            ->limit(5)
            ->get()
            ->map(fn (Budget $budget) => new SearchResult(
                type: 'budget',
                id: $budget->id,
                title: ($budget->category?->name ?? '—').' — '.Str::ucfirst($budget->month->translatedFormat('F Y.')),
                url: BudgetResource::getUrl('edit', ['record' => $budget, 'tenant' => $household]),
                icon: 'heroicon-o-chart-pie',
            ));

        return $bills->concat($transactions)->concat($categories)->concat($budgets);
    }

    public function type(): string
    {
        return 'finance';
    }
}
