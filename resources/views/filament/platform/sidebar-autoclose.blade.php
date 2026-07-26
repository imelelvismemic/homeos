{{-- Bočni meni na mobilnom pamti stanje otvorenosti (Filamentov Alpine store je
     perzistentan). Stavke menija ga same zatvore na klik, ali linkovi izvan liste
     — npr. "Postavke domaćinstva" u padajućem meniju domaćinstva, ili zvonce —
     nemaju taj handler, pa bi se nakon otvaranja stranice meni i dalje vidio
     preko sadržaja. Zatvaramo ga na klik BILO KOJEG linka koji vodi dalje. --}}
<script>
    document.addEventListener('click', function (event) {
        const link = event.target.closest('a[href]');

        if (! link || link.target === '_blank' || link.getAttribute('href')?.startsWith('#')) {
            return;
        }

        if (! window.matchMedia('(max-width: 1024px)').matches) {
            return;
        }

        window.Alpine?.store('sidebar')?.close();
    }, true);
</script>
