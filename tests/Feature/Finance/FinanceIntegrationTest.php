<?php

use App\Modules\Finance\Dashboard\FinanceDashboardWidget;
use App\Modules\Finance\Models\Bill;
use App\Platform\Calendar\CalendarService;
use App\Platform\Search\SearchService;
use Filament\Facades\Filament;

/**
 * "Sve je povezano": račun s dospijećem se automatski pojavi na kalendaru,
 * dashboardu i u pretrazi — i (DoD Faze 5) generiše podsjetnik — bez ručnog
 * povezivanja. Svi čitaju isti Bill preko registryja / CalendarSourceContract.
 */
beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('app'));
});

it('shows a bill on the calendar, dashboard and search automatically', function () {
    [$household, $owner] = makeHousehold();
    test()->actingAs($owner->user);
    Filament::setTenant($household);

    Bill::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'title' => 'Internet',
        'amount' => 35.00,
        'due_date' => now()->addDays(5)->toDateString(),
        'remind_days_before' => 3,
    ]);

    // Kalendar (agregira preko CalendarSourceContract)
    $events = app(CalendarService::class)->eventsBetween(now()->subWeek(), now()->addWeeks(2), $household);
    expect($events->contains(fn ($e) => $e->type === 'bill' && str_contains($e->title, 'Internet')))->toBeTrue();

    // Pretraga (agregira preko SearchProviderContract)
    $results = app(SearchService::class)->search('Internet', $household);
    expect($results->contains(fn ($r) => $r->type === 'bill'))->toBeTrue();

    // Dashboard widget (neplaćeni računi)
    expect(app(FinanceDashboardWidget::class)->hasContentFor($household))->toBeTrue();
});
