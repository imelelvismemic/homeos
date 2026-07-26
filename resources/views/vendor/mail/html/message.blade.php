{{--
    Okvir svakog emaila (Faza 9c). Zaglavlje nosi znak i naziv aplikacije, a
    podnožje isti potpis kao aplikacija — „©elvismemic v<verzija>", iz
    `config/homeos.php`, da se verzija u emailu i na ekranu ne mogu razići.

    Naziv dolazi iz `config('homeos.name')`, ne iz `APP_NAME`: naziv živi u kodu
    (Faza 9a), a `.env` na serveru se ne mijenja deployem.
--}}
<x-mail::layout>
{{-- Header --}}
<x-slot:header>
<x-mail::header :url="config('app.url')">
{{ config('homeos.name') }}
</x-mail::header>
</x-slot:header>

{{-- Body --}}
{!! $slot !!}

{{-- Subcopy --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{!! $subcopy !!}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

{{-- Footer --}}
<x-slot:footer>
<x-mail::footer>
{{ '©elvismemic v'.config('homeos.version') }}
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
