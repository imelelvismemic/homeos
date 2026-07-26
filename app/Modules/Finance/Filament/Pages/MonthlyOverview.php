<?php

namespace App\Modules\Finance\Filament\Pages;

use App\Modules\Finance\Enums\TransactionType;
use App\Modules\Finance\Models\Budget;
use App\Modules\Finance\Models\Category;
use App\Modules\Finance\Models\Transaction;
use App\Modules\Finance\Services\BalanceService;
use App\Modules\Finance\Support\Money;
use App\Platform\Filament\Concerns\BelongsToModule;
use App\Platform\Models\Household;
use App\Platform\Models\HouseholdMember;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Mjesečni pregled (ORIGINAL_SPEC): prihod/rashod tekućeg mjeseca, potrošeno po
 * kategoriji naspram budžeta, i "ko duguje kome" (BalanceService). Čita žive
 * Finance podatke; ne duplira ništa.
 */
class MonthlyOverview extends Page
{
    use BelongsToModule;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $slug = 'finansije-pregled';

    protected static string $view = 'filament.finance.pages.overview';

    protected static ?int $navigationSort = 0;

    /** Odabrani mjesec (prvi dan mjeseca, Y-m-d) — navigacija prethodni/sljedeći. */
    public ?string $period = null;

    public function mount(): void
    {
        $this->period = Carbon::now()->startOfMonth()->toDateString();
    }

    public static function getNavigationLabel(): string
    {
        return __('finance.overview.title');
    }

    public function getTitle(): string
    {
        return __('finance.overview.title').' — '.$this->periodLabel();
    }

    private function periodDate(): Carbon
    {
        return $this->period !== null
            ? Carbon::parse($this->period)->startOfMonth()
            : Carbon::now()->startOfMonth();
    }

    public function periodLabel(): string
    {
        return Str::ucfirst($this->periodDate()->translatedFormat('F Y.'));
    }

    /** Ne dopuštamo prelazak u budućnost — najdalje do tekućeg mjeseca. */
    public function canGoNext(): bool
    {
        return $this->periodDate()->lt(Carbon::now()->startOfMonth());
    }

    public function previousMonth(): void
    {
        $this->period = $this->periodDate()->subMonthNoOverflow()->toDateString();
    }

    public function nextMonth(): void
    {
        if ($this->canGoNext()) {
            $this->period = $this->periodDate()->addMonthNoOverflow()->toDateString();
        }
    }

    public static function getNavigationGroup(): ?string
    {
        return __('finance.navigation_group');
    }

    private function household(): ?Household
    {
        $tenant = Filament::getTenant();

        return $tenant instanceof Household ? $tenant : null;
    }

    private function start(): Carbon
    {
        return $this->periodDate()->startOfMonth();
    }

    private function end(): Carbon
    {
        return $this->periodDate()->endOfMonth();
    }

    /** @return array<string, float> */
    public function totals(): array
    {
        $household = $this->household();

        if ($household === null) {
            return ['income' => 0.0, 'expense' => 0.0, 'net' => 0.0];
        }

        $base = fn (TransactionType $type) => (float) Transaction::query()
            ->where('household_id', $household->id)
            ->visibleTo(auth()->user())
            ->where('type', $type->value)
            ->whereBetween('date', [$this->start(), $this->end()])
            ->sum('amount');

        $income = $base(TransactionType::Income);
        $expense = $base(TransactionType::Expense);

        return ['income' => $income, 'expense' => $expense, 'net' => $income - $expense];
    }

    /** @return array<int, array{name: string, spent: float, budget: ?float}> */
    public function categoryRows(): array
    {
        $household = $this->household();

        if ($household === null) {
            return [];
        }

        $spentByCategory = Transaction::query()
            ->where('household_id', $household->id)
            ->visibleTo(auth()->user())
            ->where('type', TransactionType::Expense->value)
            ->whereBetween('date', [$this->start(), $this->end()])
            ->selectRaw('category_id, SUM(amount) as spent')
            ->groupBy('category_id')
            ->pluck('spent', 'category_id');

        $budgets = Budget::query()
            ->where('household_id', $household->id)
            ->whereDate('month', $this->start()->toDateString())
            ->pluck('amount', 'category_id');

        $rows = Category::query()
            ->where('household_id', $household->id)
            ->orderBy('name')
            ->get()
            ->map(fn (Category $category) => [
                'name' => $category->name,
                'spent' => (float) ($spentByCategory[$category->id] ?? 0),
                'budget' => isset($budgets[$category->id]) ? (float) $budgets[$category->id] : null,
            ])
            ->filter(fn (array $row) => $row['spent'] > 0 || $row['budget'] !== null)
            ->values()
            ->all();

        // Nekategorisani troškovi.
        $uncategorized = (float) ($spentByCategory[null] ?? $spentByCategory[''] ?? 0);

        if ($uncategorized > 0) {
            $rows[] = ['name' => __('finance.overview.uncategorized'), 'spent' => $uncategorized, 'budget' => null];
        }

        return $rows;
    }

    /** @return array<int, array{name: string, net: float}> */
    public function balanceRows(): array
    {
        $household = $this->household();

        if ($household === null) {
            return [];
        }

        $balances = app(BalanceService::class)->balances($household, $this->start(), $this->end());

        if ($balances->isEmpty()) {
            return [];
        }

        $names = HouseholdMember::query()
            ->whereIn('id', $balances->keys())
            ->with('user')
            ->get()
            ->mapWithKeys(fn (HouseholdMember $m) => [$m->id => $m->user?->name ?? '—']);

        return $balances
            ->map(fn (float $net, int $memberId) => ['name' => $names[$memberId] ?? '—', 'net' => $net])
            ->sortByDesc('net')
            ->values()
            ->all();
    }

    public function money(float $value): string
    {
        return Money::km($value);
    }
}
