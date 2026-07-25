<?php

namespace App\Modules\Finance\Filament\Widgets;

use App\Modules\Finance\Dashboard\FinanceDashboardWidget;
use App\Modules\Finance\Models\Bill;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class UpcomingBillsWidget extends Widget
{
    protected static string $view = 'filament.finance.widgets.upcoming-bills';

    protected int|string|array $columnSpan = 'full';

    /**
     * @return Collection<int, Bill>
     */
    public function getBills(): Collection
    {
        $household = Filament::getTenant();

        if ($household === null) {
            return collect();
        }

        return FinanceDashboardWidget::relevantQuery($household)
            ->orderBy('due_date')
            ->limit(5)
            ->get();
    }
}
