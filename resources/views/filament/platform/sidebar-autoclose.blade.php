{{-- Bočni meni pamti stanje otvorenosti (Filamentov Alpine store je perzistentan,
     u localStorage). Dvije posljedice, oba puta meni završi preko sadržaja:

     1. Stavke menija ga same zatvore na klik, ali linkovi izvan liste — npr.
        „Postavke domaćinstva" u padajućem meniju domaćinstva, ili zvonce — nemaju
        taj handler, pa bi se nakon otvaranja stranice meni i dalje vidio.

     2. Zapamćeno stanje „otvoren" sa širokog ekrana vrijedi i kad je prikaz uži od
        1024px — tada Filament meni renderuje kao PREKLAPAJUĆI panel sa zatamnjenom
        pozadinom, pa stranica pri učitavanju izgleda kao da je nešto puklo.
        Prijavljeno na Linuxu s uvećanim prikazom: skaliranje ekrana smanji CSS
        širinu ispod granice, pa se na fizički širokom monitoru dobije uži prikaz.

     Zato je na užem prikazu meni pri učitavanju UVIJEK zatvoren, i zatvara se na
     klik bilo kojeg linka koji vodi dalje. Na desktopu se ne dira. --}}
<script>
    (function () {
        // Ista granica kao u temi (`@media (max-width: 1023px)`) i kao Filamentov
        // `lg`. Ranije je ovdje stajalo 1024px, pa se na točno toj širini CSS
        // ponašao kao desktop a skripta kao mobilni.
        const narrow = window.matchMedia('(max-width: 1023px)');

        function closeIfNarrow() {
            if (narrow.matches) {
                window.Alpine?.store('sidebar')?.close();
            }
        }

        // Pri učitavanju i nakon SPA navigacije (Filament koristi wire:navigate).
        document.addEventListener('alpine:initialized', closeIfNarrow);
        document.addEventListener('livewire:navigated', closeIfNarrow);

        // Alpine može biti inicijalizovan prije nego ova skripta stigne, pa se
        // provjerava i odmah.
        closeIfNarrow();

        // Prelazak sa širokog na uži prikaz (promjena veličine prozora, zoom,
        // rotacija) — bez ovoga otvoreni meni ostane preko sadržaja.
        narrow.addEventListener('change', closeIfNarrow);

        document.addEventListener('click', function (event) {
            const link = event.target.closest('a[href]');

            if (! link || link.target === '_blank' || link.getAttribute('href')?.startsWith('#')) {
                return;
            }

            closeIfNarrow();
        }, true);
    })();
</script>
