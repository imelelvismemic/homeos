<?php

use App\Modules\LifeAdmin\Dashboard\LifeAdminDashboardWidget;
use App\Modules\LifeAdmin\Enums\DocumentType;
use App\Modules\LifeAdmin\Models\Document;
use App\Platform\Calendar\CalendarService;
use App\Platform\Search\SearchService;
use Filament\Facades\Filament;

/**
 * "Sve je povezano": dokument s datumom isteka se automatski pojavi na kalendaru,
 * dashboardu i u pretrazi — i (DoD Faze 5b) generiše podsjetnik — bez ručnog
 * povezivanja. Svi čitaju isti Document preko registryja / contract-a.
 */
beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('app'));
});

it('shows an expiring document on the calendar, dashboard and search automatically', function () {
    [$household, $owner] = makeHousehold();
    test()->actingAs($owner->user);
    Filament::setTenant($household);

    Document::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'type' => DocumentType::Renewal,
        'title' => 'Lična karta',
        'expiry_date' => now()->addDays(20)->toDateString(),
        'remind_days_before' => 30,
    ]);

    // Kalendar (agregira preko CalendarSourceContract)
    $events = app(CalendarService::class)->eventsBetween(now()->subWeek(), now()->addWeeks(4), $household);
    expect($events->contains(fn ($e) => $e->type === 'document' && str_contains($e->title, 'Lična karta')))->toBeTrue();

    // Pretraga (agregira preko SearchProviderContract)
    $results = app(SearchService::class)->search('Lična', $household);
    expect($results->contains(fn ($r) => $r->type === 'document'))->toBeTrue();

    // Dashboard widget (uskoro ističe)
    expect(app(LifeAdminDashboardWidget::class)->hasContentFor($household))->toBeTrue();
});
