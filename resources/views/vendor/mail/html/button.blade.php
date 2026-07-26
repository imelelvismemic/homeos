@props([
    'url',
    'color' => 'primary',
    'align' => 'center',
])
{{--
    Dugme u emailu (ROADMAP Faza 9c, ispravka nakon provjere u Outlooku).

    Boje i dimenzije su INLINE, ne kroz CSS klasu. Dva razloga:
     1. Laravelov CSS inliner primjenjuje i pravilo `.inner-body a`, koje je
        specifičnije od `.button` — pa je tekst dugmeta dobijao boju linka
        (terakota na terakoti, tekst se gubio).
     2. `.button-primary` u default temi pravi visinu kroz debele `border`-e iste
        boje kao podloga; uz naš `padding` je to davalo dvostruki okvir koji se
        vidio kao svjetliji prsten oko dugmeta.

    Boja dolazi iz jedne mape ispod, pa i dalje postoji jedno mjesto istine.
--}}
@php
    $backgrounds = [
        'primary' => '#bf6a44',
        'blue' => '#bf6a44',
        'success' => '#4e8d5b',
        'green' => '#4e8d5b',
        'error' => '#b23b2e',
        'red' => '#b23b2e',
    ];

    $background = $backgrounds[$color] ?? $backgrounds['primary'];
@endphp
<table class="action" align="{{ $align }}" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="{{ $align }}">
<table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="{{ $align }}">
<table border="0" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="center" bgcolor="{{ $background }}" style="background-color: {{ $background }}; border-radius: 10px;">
<a href="{{ $url }}" target="_blank" rel="noopener" style="display: inline-block; padding: 13px 26px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 15px; font-weight: 600; line-height: 1; color: #ffffff; text-decoration: none; border-radius: 10px;">{!! $slot !!}</a>
</td>
</tr>
</table>
</td>
</tr>
</table>
</td>
</tr>
</table>
