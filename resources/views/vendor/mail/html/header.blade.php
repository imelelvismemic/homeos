@props(['url'])
{{--
    Zaglavlje emaila (ROADMAP Faza 9c): ORIGINALNI znak aplikacije + naziv.

    Znak je isti `public/favicon.svg` koji nosi aplikacija, rasterizovan u
    `public/email-logo.png` (144 px, prikazuje se na 36 px — dvostruka
    rezolucija radi retina ekrana). Rasterizacija je nužna, ne izbor: Gmail u
    potpunosti izbacuje `<svg>` iz emaila, a Outlook ga ne renderuje, pa bi
    originalni SVG (i kao `data:` URI) ostao nevidljiv kod većine primalaca.
    Ako se znak ikad promijeni, PNG se regeneriše iz istog SVG-a.

    Uz sliku stoji i naziv kao tekst — ako klijent blokira slike dok korisnik ne
    dopusti prikaz, zaglavlje se i dalje čita, bez praznog okvira.
--}}
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block; text-decoration: none;">
<table cellpadding="0" cellspacing="0" role="presentation" align="center" style="margin: 0 auto;">
<tr>
<td style="padding-right: 10px; vertical-align: middle; line-height: 1;">
<img src="{{ rtrim(config('app.url'), '/').'/email-logo.png' }}" width="36" height="36" alt="{{ config('homeos.name') }}" style="display: block; width: 36px; height: 36px; border: 0;">
</td>
<td style="vertical-align: middle; text-align: left;">
<span class="brand-name">{{ \Illuminate\Support\Str::beforeLast(config('homeos.name'), ' ') }}</span><span class="brand-name brand-name-accent">&#32;{{ \Illuminate\Support\Str::afterLast(config('homeos.name'), ' ') }}</span>
</td>
</tr>
</table>
</a>
</td>
</tr>
