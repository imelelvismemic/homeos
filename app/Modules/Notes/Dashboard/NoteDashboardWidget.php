<?php

namespace App\Modules\Notes\Dashboard;

use App\Modules\Notes\Filament\Widgets\RecentNotesWidget;
use App\Modules\Notes\Models\Note;
use App\Platform\Contracts\DashboardWidgetContract;
use App\Platform\Models\Household;
use Illuminate\Database\Eloquent\Builder;

/**
 * Prijava Bilješki na "Today" dashboard (CLAUDE.md §7) — prikazuje zadnje bilješke.
 */
class NoteDashboardWidget implements DashboardWidgetContract
{
    public function title(): string
    {
        return __('notes.plural_label');
    }

    public function widgetClass(): string
    {
        return RecentNotesWidget::class;
    }

    public function hasContentFor(Household $household): bool
    {
        return static::relevantQuery($household)->exists();
    }

    public static function relevantQuery(Household $household): Builder
    {
        return Note::query()
            ->where('household_id', $household->id)
            ->visibleTo(auth()->user())
            ->latest('updated_at');
    }
}
