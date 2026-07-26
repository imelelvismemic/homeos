<?php

use App\Modules\Reminders\Models\Reminder;
use App\Modules\Reminders\Notifications\ReminderDue;
use App\Platform\Filament\Pages\Dashboard;
use App\Platform\Localization\Locales;
use App\Platform\Modules\ModuleRegistry;
use App\Platform\QuickCapture\QuickCaptureRegistry;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('app'));
});

/** Spljošti ugniježđeni prijevodni niz u listu punih ključeva (a.b.c). */
function translationKeys(array $lines, string $prefix = ''): array
{
    $keys = [];

    foreach ($lines as $key => $value) {
        $full = $prefix === '' ? (string) $key : "{$prefix}.{$key}";

        if (is_array($value)) {
            $keys = [...$keys, ...translationKeys($value, $full)];

            continue;
        }

        $keys[] = $full;
    }

    sort($keys);

    return $keys;
}

it('has the same translation keys in every language', function () {
    // Nedostajući ključ se u UI-ju ne vidi kao greška nego kao sirovi tekst
    // ("tasks.fields.title"), i to obično tek na stranici koju niko ne otvara
    // često — zato je parnost test, ne stvar pažnje pri prevođenju.
    $files = collect(glob(lang_path('bs/*.php')))
        ->map(fn (string $path): string => basename($path))
        // validation.php je poseban: bs je izbor iz Laravelovog seta pravila,
        // a en/de nose puni set (vidi zaseban test ispod).
        ->reject(fn (string $file): bool => $file === 'validation.php');

    expect($files)->not->toBeEmpty();

    foreach ($files as $file) {
        $bs = translationKeys(require lang_path("bs/{$file}"));

        foreach (['en', 'de'] as $locale) {
            $path = lang_path("{$locale}/{$file}");

            expect(file_exists($path))->toBeTrue("Nedostaje lang/{$locale}/{$file}");
            expect(translationKeys(require $path))->toBe($bs, "Ključevi se razilaze: {$locale}/{$file}");
        }
    }
});

it('covers every validation rule the other languages define', function () {
    $en = translationKeys(require lang_path('en/validation.php'));
    $de = translationKeys(require lang_path('de/validation.php'));
    $bs = translationKeys(require lang_path('bs/validation.php'));

    // en je kopija Laravelovog seta; de mora imati isti opseg, inače korisnik
    // na njemačkom dobije sirovi ključ za pravilo koje bs slučajno ne koristi.
    expect($de)->toBe($en);
    expect(array_diff($bs, $en))->toBe([]);
});

it('has the same JSON translation keys in every language', function () {
    $bs = array_keys(json_decode(file_get_contents(lang_path('bs.json')), true));
    $de = array_keys(json_decode(file_get_contents(lang_path('de.json')), true));

    sort($bs);
    sort($de);

    // Za engleski JSON fajl ne treba: ključ JE engleski tekst.
    expect($de)->toBe($bs);
});

it('resolves every user-visible string in the app registry in all languages', function () {
    // `config/homeos-apps.php` nosi prijevodne ključeve, ne gotov tekst: config se
    // u produkciji kešira, pa bi `__()` u configu zamrznuo jezik onoga ko je pravio
    // keš. Posljedica pogrešnog ključa nije greška nego sirovi ključ na ekranu —
    // zato se ovdje traži da se SVAKI ključ stvarno razriješi, u svim jezicima.
    $texts = [];

    foreach (config('homeos-apps') as $moduleKey => $app) {
        $texts["{$moduleKey}.name"] = $app['name'];

        $definitions = $app['quick_capture'] ?? null;

        if (! is_array($definitions)) {
            continue;
        }

        foreach (array_is_list($definitions) ? $definitions : [$definitions] as $i => $definition) {
            $texts["{$moduleKey}.quick_capture.{$i}.label"] = $definition['label'];

            foreach ($definition['fields'] ?? [] as $field) {
                $texts["{$moduleKey}.quick_capture.{$i}.{$field['name']}"] = $field['label'];
            }
        }
    }

    expect($texts)->not->toBeEmpty();

    foreach ($texts as $where => $key) {
        foreach (['bs', 'en', 'de'] as $locale) {
            expect(trans($key, [], $locale))
                ->not->toBe($key, "Ključ se ne razrješava ({$locale}): {$where} → {$key}");
        }
    }
});

it('renders the app names and quick-add buttons in the chosen language', function () {
    [$household, $owner] = makeHousehold();
    $owner->user->update(['locale' => 'de']);

    test()->actingAs($owner->user);
    Filament::setTenant($household);
    app()->setLocale('de');

    expect(app(ModuleRegistry::class)->name('tasks'))->toBe('Aufgaben');

    $labels = app(QuickCaptureRegistry::class)->items()->pluck('label')->all();

    expect($labels)->toContain('Neue Aufgabe');
    expect($labels)->not->toContain('Novi zadatak');
});

it('remembers the chosen language for a guest and for a signed-in user', function () {
    test()->post(route('locale', ['locale' => 'de']))
        ->assertRedirect();

    expect(session('locale'))->toBe('de');

    [$household, $owner] = makeHousehold();

    test()->actingAs($owner->user)
        ->post(route('locale', ['locale' => 'en']))
        ->assertRedirect();

    // Prijavljenom korisniku izbor ide u bazu — prati ga na drugi uređaj i u
    // email obavještenja.
    expect($owner->user->fresh()->locale)->toBe('en');
});

it('rejects a language it does not support', function () {
    test()->post(route('locale', ['locale' => 'fr']))->assertNotFound();

    expect(Locales::sanitize('fr'))->toBe(Locales::DEFAULT);
});

it('renders the panel in the language the user chose', function () {
    [$household, $owner] = makeHousehold();
    $owner->user->update(['locale' => 'de']);

    test()->actingAs($owner->user);

    // Puni HTTP zahtjev, ne Livewire::test — jezik postavlja middleware panela,
    // pa se samo tako provjerava da je lanac stvarno spojen.
    test()->get(Dashboard::getUrl(tenant: $household))
        ->assertOk()
        ->assertSee('Guten')          // pozdrav po dobu dana
        ->assertDontSee('Dobro jutro');
});

it('shows the language switcher on the sign-in page', function () {
    test()->get(route('filament.app.auth.login'))
        ->assertOk()
        ->assertSee('Bosanski')
        ->assertSee('English')
        ->assertSee('Deutsch');
});

it('keeps the navigation groups in order after the language changes', function () {
    [$household, $owner] = makeHousehold();
    $owner->user->update(['locale' => 'de']);

    test()->actingAs($owner->user);
    Filament::setTenant($household);
    app()->setLocale('de');

    $groups = collect(Filament::getPanel('app')->getNavigationGroups())
        ->map(fn ($group) => $group->getLabel())
        ->values()
        ->all();

    // Labele grupa se računaju pri gradnji panela (prije middleware-a), pa bez
    // closure-a ostaju bosanske i redoslijed menija se raspada na drugom jeziku.
    expect($groups)->toBe(['Organisation', 'Finanzen', 'Verwaltung']);
});

it('sends the email in the language of the person receiving it', function () {
    [$household, $owner] = makeHousehold();
    $owner->user->update(['locale' => 'de']);

    // Scheduler radi na jeziku aplikacije, ne primaoca.
    app()->setLocale('bs');

    $reminder = Reminder::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'title' => 'Termin',
        'due_date' => now(),
    ]);

    // NAMJERNO bez Notification::fake(): fake ne poziva toMail(), pa bi test
    // prošao i da jezik primaoca uopšte ne radi (docs/RULES.md).
    config()->set('mail.default', 'array');
    $transport = Mail::mailer('array')->getSymfonyTransport();
    $transport->flush();

    $owner->notify(new ReminderDue($reminder));

    $subjects = collect($transport->messages())
        ->map(fn ($message) => $message->getOriginalMessage()->getSubject())
        ->all();

    expect($subjects)->toContain(__('reminders.notifications.due.subject', [], 'de'));
    expect(app()->getLocale())->toBe('bs'); // jezik procesa se vraća nakon slanja
});
