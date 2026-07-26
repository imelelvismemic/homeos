@props(['url'])
{{--
    Zaglavlje emaila (ROADMAP Faza 9c): znak aplikacije + naziv, u paleti teme.

    Znak je NAMJERNO složen HTML tabelom i bojama, a ne kao SVG ili slika: Gmail
    izbacuje `<svg>` iz emaila, Outlook ga ne renderuje, a vanjske slike većina
    klijenata blokira dok korisnik ne dopusti prikaz — pa bi na mjestu logotipa
    stajao prazan okvir. Ovako se znak vidi svuda i odmah, bez ijednog vanjskog
    zahtjeva, i prati isti krov-s-plusom motiv iz aplikacije.
--}}
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block; text-decoration: none;">
<table cellpadding="0" cellspacing="0" role="presentation" align="center" style="margin: 0 auto;">
<tr>
<td style="padding-right: 10px; vertical-align: middle;">
<table cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td class="brand-mark" width="36" height="36" align="center" valign="middle">+</td>
</tr>
</table>
</td>
<td style="vertical-align: middle; text-align: left;">
<span class="brand-name">{{ \Illuminate\Support\Str::beforeLast(config('homeos.name'), ' ') }}</span><span class="brand-name brand-name-accent">&#32;{{ \Illuminate\Support\Str::afterLast(config('homeos.name'), ' ') }}</span>
</td>
</tr>
</table>
</a>
</td>
</tr>
