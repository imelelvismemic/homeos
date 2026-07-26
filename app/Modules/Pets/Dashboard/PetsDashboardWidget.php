<?php

namespace App\Modules\Pets\Dashboard;

use App\Modules\Pets\Filament\Widgets\UpcomingCareWidget;
use App\Modules\Pets\Models\CareRecord;
use App\Platform\Contracts\DashboardWidgetContract;
use App\Platform\Models\Household;
use Illuminate\Database\Eloquent\Builder;

/**
 * Prijava Ljubimaca na "Today" dashboard (CLAUDE.md §7) — njega koja dolazi
 * uskoro ili je propuštena.
 */
class PetsDashboardWidget implements DashboardWidgetContract
{
    /** Prozor "uskoro" — njega u narednih toliko dana (ili već prošla). */
    public const SOON_DAYS = 14;

    public function title(): string
    {
        return __('pets.widget.heading');
    }

    public function widgetClass(): string
    {
        return UpcomingCareWidget::class;
    }

    public function hasContentFor(Household $household): bool
    {
        return static::relevantQuery($household)->exists();
    }

    /** Nezavršena njega s terminom koji je prošao ili je unutar SOON_DAYS. */
    public static function relevantQuery(Household $household): Builder
    {
        return CareRecord::query()
            ->where('household_id', $household->id)
            ->visibleTo(auth()->user())
            ->whereNull('completed_at')
            ->whereDate('due_date', '<=', now()->addDays(self::SOON_DAYS));
    }
}
