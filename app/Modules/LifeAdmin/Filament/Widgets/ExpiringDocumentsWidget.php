<?php

namespace App\Modules\LifeAdmin\Filament\Widgets;

use App\Modules\LifeAdmin\Dashboard\LifeAdminDashboardWidget;
use App\Modules\LifeAdmin\Models\Document;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class ExpiringDocumentsWidget extends Widget
{
    protected static string $view = 'filament.lifeadmin.widgets.expiring-documents';

    protected int|string|array $columnSpan = 'full';

    /**
     * @return Collection<int, Document>
     */
    public function getDocuments(): Collection
    {
        $household = Filament::getTenant();

        if ($household === null) {
            return collect();
        }

        return LifeAdminDashboardWidget::relevantQuery($household)
            ->orderBy('expiry_date')
            ->limit(5)
            ->get();
    }
}
