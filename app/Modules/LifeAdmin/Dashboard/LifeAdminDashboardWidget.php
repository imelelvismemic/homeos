<?php

namespace App\Modules\LifeAdmin\Dashboard;

use App\Modules\LifeAdmin\Filament\Widgets\ExpiringDocumentsWidget;
use App\Modules\LifeAdmin\Models\Document;
use App\Platform\Contracts\DashboardWidgetContract;
use App\Platform\Models\Household;
use Illuminate\Database\Eloquent\Builder;

/**
 * Prijava Life admina na "Today" dashboard (CLAUDE.md §7) — dokumenti kojima
 * uskoro ističe rok (ili je već istekao).
 */
class LifeAdminDashboardWidget implements DashboardWidgetContract
{
    /** Prozor "uskoro ističe" — dokumenti s istekom unutar ovoliko dana (ili prošli). */
    public const SOON_DAYS = 60;

    public function title(): string
    {
        return __('lifeadmin.widget.heading');
    }

    public function widgetClass(): string
    {
        return ExpiringDocumentsWidget::class;
    }

    public function hasContentFor(Household $household): bool
    {
        return static::relevantQuery($household)->exists();
    }

    /** Dokumenti s istekom koji je prošao ili je unutar SOON_DAYS. */
    public static function relevantQuery(Household $household): Builder
    {
        return Document::query()
            ->where('household_id', $household->id)
            ->visibleTo(auth()->user())
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', now()->addDays(self::SOON_DAYS));
    }
}
