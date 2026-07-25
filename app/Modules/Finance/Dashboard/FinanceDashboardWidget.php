<?php

namespace App\Modules\Finance\Dashboard;

use App\Modules\Finance\Filament\Widgets\UpcomingBillsWidget;
use App\Modules\Finance\Models\Bill;
use App\Platform\Contracts\DashboardWidgetContract;
use App\Platform\Models\Household;
use Illuminate\Database\Eloquent\Builder;

/**
 * Prijava Finansija na "Today" dashboard (CLAUDE.md §7) — nadolazeći/neplaćeni računi.
 */
class FinanceDashboardWidget implements DashboardWidgetContract
{
    public function title(): string
    {
        return __('finance.bills.plural_label');
    }

    public function widgetClass(): string
    {
        return UpcomingBillsWidget::class;
    }

    public function hasContentFor(Household $household): bool
    {
        return static::relevantQuery($household)->exists();
    }

    /** Neplaćeni računi. */
    public static function relevantQuery(Household $household): Builder
    {
        return Bill::query()
            ->where('household_id', $household->id)
            ->visibleTo(auth()->user())
            ->whereNull('paid_at');
    }
}
