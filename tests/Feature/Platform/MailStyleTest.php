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

    return collect($transport->messages())
        ->map(fn ($message) => $message->getOriginalMessage()->toString())
        ->implode("\n");
}

it('brands every email with the app mark, name and signature', function () {
    $html = renderReminderMail();

    // Znak: terakota kvadrat s plusom, složen HTML-om (Gmail izbacuje <svg>,
    // a vanjske slike su blokirane dok korisnik ne dopusti prikaz).
    expect($html)->toContain('brand-mark');
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
