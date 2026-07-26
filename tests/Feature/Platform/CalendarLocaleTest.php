<?php

use App\Modules\Calendar\Filament\Pages\CalendarPage;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\File;

/**
 * Jezik kalendara (ispravka nakon predaje).
 *
 * Nazivi mjeseci i dana su bili ispisani kao fiksni bosanski nizovi u
 * `resources/js/calendar.js`, pa su na engleskom i njemačkom ostajali na
 * bosanskom — kalendar je bio jedini ekran koji nije pratio izbor jezika.
 * Sada ih formatira FullCalendar iz aktivnog jezika, a terminologija dugmadi
 * dolazi iz `lang/<jezik>/calendar.php`.
 */
beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('app'));
});

it('passes the active language and translated labels to the calendar', function () {
    [$household, $owner] = makeHousehold();
    $owner->user->update(['locale' => 'de']);
    test()->actingAs($owner->user);

    $html = test()->get(CalendarPage::getUrl(tenant: $household))->assertOk()->getContent();

    // Bez jezika FullCalendar formatira mjesece i dane na engleskom.
    expect($html)->toContain('"locale":"de"');

    // Terminologija dugmadi je naša (bundle za de nudi „Terminübersicht" za listu).
    expect($html)->toContain('Monat');
    expect($html)->toContain('Woche');
    expect($html)->toContain('Liste');
    expect($html)->toContain('Ganzt');          // Ganztägig, bez ovisnosti o kodiranju
    expect($html)->not->toContain('Sedmica');
});

it('falls back to the household language when the member has not chosen one', function () {
    [$household, $owner] = makeHousehold();
    test()->actingAs($owner->user);

    $html = test()->get(CalendarPage::getUrl(tenant: $household))->assertOk()->getContent();

    expect($html)->toContain('"locale":"bs"');
    expect($html)->toContain('Mjesec');
    expect($html)->toContain('Sedmica');
});

it('never hardcodes month or day names in the calendar script', function () {
    $js = File::get(resource_path('js/calendar.js'));

    // Ovo je greška koja se vraća „sama": neko doda naziv da bi popravio jedan
    // prikaz i time ga zamrzne na jednom jeziku. Nazivi smiju doći isključivo iz
    // locale sloja biblioteke.
    foreach (['januar', 'februar', 'decembar', 'nedjelja', 'ponedjeljak', 'srijeda'] as $name) {
        expect($js)->not->toContain("'{$name}'");
    }

    // Locale bundlovi za bs i de moraju biti uvezeni; en je ugrađen u biblioteku.
    expect($js)->toContain('locales/bs');
    expect($js)->toContain('locales/de');
});

it('completes the Bosnian locale, which the library ships without hints', function () {
    $js = File::get(resource_path('js/calendar.js'));

    // Bosanski bundle nema buttonHints/viewHint, pa je biblioteka padala na
    // engleski šablon i miješala jezike: „Previous Mjesec", „This Mjesec".
    expect($js)->toContain('buttonHints');
    expect($js)->toContain('Prethodni');
    expect($js)->toContain('Sljedeći');
    expect($js)->toContain('viewHint');

    // Rod mijenja pridjev („prethodni mjesec" vs „prethodna sedmica") — bez toga
    // bi hintovi bili gramatički pogrešni, što čitač ekrana čita naglas.
    expect($js)->toContain('Prethodna');
    expect($js)->toContain('Sljedeća');
});
