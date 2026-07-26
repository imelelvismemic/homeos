<?php

use App\Modules\Reminders\Models\Reminder;
use App\Modules\Reminders\Notifications\ReminderDue;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Mail;

/**
 * Izgled emailova (ROADMAP Faza 9c). Sve ide kroz STVARNI mailer (`array`
 * transport), ne `Notification::fake()` — fake nikad ne pozove `toMail()`, pa ni
 * jedan šablon ne bi bio ni renderovan (RULES.md §11).
 */
beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('app'));
    config()->set('mail.default', 'array');
});

function renderReminderMail(): string
{
    [$household, $owner] = makeHousehold();

    $reminder = Reminder::create([
        'household_id' => $household->id,
        'created_by' => $owner->user_id,
        'title' => 'Termin kod ljekara',
        'due_date' => now(),
    ]);

    $transport = Mail::mailer('array')->getSymfonyTransport();
    $transport->flush();

    $owner->notify(new ReminderDue($reminder));

    // Tijelo je quoted-printable kodirano (`alt=3D"..."`, `=` prelama redove), pa
    // se dekodira — inače tvrdnje provjeravaju kodiranje, a ne sadržaj.
    return quoted_printable_decode(
        collect($transport->messages())
            ->map(fn ($message) => $message->getOriginalMessage()->toString())
            ->implode("\n")
    );
}

it('brands every email with the app mark, name and signature', function () {
    $html = renderReminderMail();

    // Znak je ORIGINALNI logo aplikacije, rasterizovan iz istog SVG-a
    // (Gmail izbacuje <svg> iz emaila, pa SVG kod većine primalaca ne bi bio
    // vidljiv). Uz sliku ide i naziv kao tekst, za slučaj blokiranih slika.
    expect($html)->toContain('email-logo.png');
    expect($html)->toContain('alt="HomeOS plus"');
    expect($html)->toContain('HomeOS');
    expect($html)->toContain('#bf6a44');

    // Potpis je isti kao u aplikaciji i iz istog izvora (config/homeos.php),
    // pa verzija u emailu i na ekranu ne mogu odstupiti.
    expect($html)->toContain('elvismemic v'.config('homeos.version'));

    // Laravelov default izgled ne smije ostati.
    expect($html)->not->toContain('notification-logo');
    expect($html)->not->toContain('All rights reserved');
});

it('uses the app palette, not the default Laravel mail theme', function () {
    $html = renderReminderMail();

    // Tema se bira u config/mail.php; bez toga bi se renderovao `default.css`.
    expect(config('mail.markdown.theme'))->toBe('homeos');

    // Dugme u boji teme (terakota), podloga u toplom neutralnom tonu.
    expect($html)->toContain('#f7f3ee');
    expect($html)->not->toContain('#2d3748');   // Laravelovo default tamno plavo
});

it('keeps a plain-text alternative that carries the same signature', function () {
    $raw = renderReminderMail();

    expect($raw)->toContain('text/plain');
    expect(substr_count($raw, 'elvismemic'))->toBeGreaterThan(1);
});
