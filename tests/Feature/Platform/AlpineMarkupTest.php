<?php

use App\Platform\Filament\Pages\Dashboard;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\File;

/**
 * Cjelovitost Alpine komponenti u Blade atributima (ROADMAP Faza 9c, ispravka).
 *
 * Naši modali (brzo dodavanje, univerzalna pretraga) žive u `x-data="{ ... }"` —
 * dakle cijela JavaScript komponenta je JEDAN HTML atribut. HTML atribut NE
 * poznaje `\"` escape: takav navodnik ga zatvara. Posljedica nije tiha — ostatak
 * koda se izlije na stranicu kao vidljiv tekst i modal prestane raditi na SVAKOJ
 * stranici, jer se renderuje u traci.
 *
 * Tako je i puklo: komentar unutar `x-data` je sadržavao `„Sada\"`.
 */
beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('app'));
});

it('never escapes a double quote with a backslash inside a Blade template', function () {
    $offenders = collect(File::allFiles(resource_path('views')))
        ->filter(fn ($file): bool => str_ends_with($file->getFilename(), '.blade.php'))
        ->filter(fn ($file): bool => str_contains(File::get($file->getPathname()), '\\"'))
        ->map(fn ($file): string => $file->getRelativePathname())
        ->values()
        ->all();

    // Ako je `\"` stvarno potreban (npr. u PHP stringu izvan atributa), koristi
    // `&quot;` ili jednostruke navodnike — ne dodavaj izuzetak ovdje.
    expect($offenders)->toBe([]);
});

it('renders the quick-add component as one unbroken attribute', function () {
    [$household, $owner] = makeHousehold();
    test()->actingAs($owner->user);

    $html = test()->get(Dashboard::getUrl(tenant: $household))->assertOk()->getContent();

    // `[^"]*` staje na prvom navodniku — ako je atribut presječen, uhvaćeni
    // sadržaj neće imati kraj komponente.
    preg_match_all('/x-data="([^"]*)"/s', $html, $matches);

    $quickCapture = collect($matches[1])->first(fn (string $value): bool => str_contains($value, 'openModal'));

    expect($quickCapture)->not->toBeNull('Brzo dodavanje se ne renderuje u traci.');
    expect($quickCapture)->toContain('setNow(');
    expect($quickCapture)->toContain('async submit()');
});

it('does not leak component source code into the visible page', function () {
    [$household, $owner] = makeHousehold();
    test()->actingAs($owner->user);

    $html = test()->get(Dashboard::getUrl(tenant: $household))->assertOk()->getContent();

    // Kad atribut pukne, ostatak JS-a postane tekstualni čvor i korisnik ga vidi
    // na stranici (upravo tako je i primijećeno). Zato se gleda TEXT dokumenta.
    //
    // Ovdje ne ide regex za skidanje tagova: Alpine atributi sadrže `=>` iz
    // strelica-funkcija, pa `<[^>]*>` staje na tom `>` i pola atributa proglasi
    // tekstom — prva verzija ovog testa je zbog toga lažno padala. DOM parser
    // zna razliku između atributa i teksta.
    $dom = new DOMDocument;
    @$dom->loadHTML($html, LIBXML_NOWARNING | LIBXML_NOERROR);
    $text = $dom->textContent;

    expect($text)->not->toContain('this.saving = true');
    expect($text)->not->toContain('X-CSRF-TOKEN');
    expect($text)->not->toContain('x-on:keydown');
});

it('closes the sidebar on narrow screens instead of leaving it over the content', function () {
    $script = File::get(resource_path('views/filament/platform/sidebar-autoclose.blade.php'));

    // Filament pamti stanje menija u localStorage. Zapamćeno "otvoren" sa širokog
    // ekrana na užem prikazu daje preklapajući panel sa zatamnjenom pozadinom —
    // stranica izgleda pokvareno već pri učitavanju (prijavljeno na Linuxu s
    // uvećanim prikazom, gdje skaliranje smanji CSS širinu ispod granice).
    expect($script)->toContain('alpine:initialized');
    expect($script)->toContain('livewire:navigated');
    expect($script)->toContain("narrow.addEventListener('change'");

    // Granica mora biti ISTA kao u temi; na 1024px je CSS bio desktop a skripta
    // mobilni, pa se ponašanje razilazilo točno na toj širini.
    expect($script)->toContain('max-width: 1023px');
    expect($script)->not->toContain('max-width: 1024px');

    $theme = File::get(resource_path('css/filament/app/theme.css'));
    expect($theme)->toContain('max-width: 1023px');
});
