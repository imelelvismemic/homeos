<?php

namespace App\Modules\Notes\Filament\Widgets;

use App\Modules\Notes\Dashboard\NoteDashboardWidget;
use App\Modules\Notes\Models\Note;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class RecentNotesWidget extends Widget
{
    protected static string $view = 'filament.notes.widgets.recent-notes';

    protected int|string|array $columnSpan = 'full';

    /**
     * @return Collection<int, Note>
     */
    public function getNotes(): Collection
    {
        $household = Filament::getTenant();

        if ($household === null) {
            return collect();
        }

        return NoteDashboardWidget::relevantQuery($household)
            ->limit(5)
            ->get();
    }
}
