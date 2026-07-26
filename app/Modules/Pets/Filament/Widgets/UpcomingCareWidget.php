<?php

namespace App\Modules\Pets\Filament\Widgets;

use App\Modules\Pets\Dashboard\PetsDashboardWidget;
use App\Modules\Pets\Models\CareRecord;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class UpcomingCareWidget extends Widget
{
    protected static string $view = 'filament.pets.widgets.upcoming-care';

    protected int|string|array $columnSpan = 'full';

    /**
     * @return Collection<int, CareRecord>
     */
    public function getCareRecords(): Collection
    {
        $household = Filament::getTenant();

        if ($household === null) {
            return collect();
        }

        return PetsDashboardWidget::relevantQuery($household)
            ->with('pet')
            ->orderBy('due_date')
            ->limit(5)
            ->get();
    }
}
