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

it('formats Bosnian itself and leaves the other languages to the library', function () {
    $js = File::get(resource_path('js/calendar.js'));

    // Prva verzija ove ispravke je nazive prepustila `Intl`-u za sva tri jezika i
    // bila pogrešna: pretraživači na Windowsu ne nose `bs` ICU podatke, pa je
    // naslov ispisivao CLDR root („2026 M07") uz engleske nazive dana. Zato
    // bosanski MORA imati vlastite nazive u kodu…
    expect($js)->toContain('BS_MONTHS');
    expect($js)->toContain('BS_DAYS_SHORT');
    expect($js)->toContain('BS_DAYS_LONG');

    // …ali smiju se primijeniti SAMO na bosanskom. Bez tog uslova bi engleski i
    // njemački opet dobili bosanske nazive — greška zbog koje je ovo i prijavljeno.
    expect($js)->toContain("locale === 'bs'");
    expect($js)->toContain('isBosnian');

    // Locale bundlovi za bs i de ostaju uvezeni (dugmad, hintovi, weekText).
    expect($js)->toContain('locales/bs');
    expect($js)->toContain('locales/de');
});

it('keeps the agreed Bosnian date formats in one place', function () {
    $js = File::get(resource_path('js/calendar.js'));

    // Dogovoreni format: „Juli, 2026" (naslov mjeseca, veliko slovo),
    // „26.juli, 2026" (datum) i „20 – 26.juli, 2026" (raspon).
    expect($js)->toContain('bsMonthTitle');
    expect($js)->toContain('bsFullDate');
    expect($js)->toContain('bsRange');

    // Naziv mjeseca u datumu ostaje malim slovom, veliko je samo na početku
    // naslova (RULES.md §2) — zato kapitalizacija stoji na jednom mjestu.
    expect($js)->toContain('toUpperCase');
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
