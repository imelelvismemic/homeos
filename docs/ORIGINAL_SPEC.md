<!--
Ovo je izvorni brief projekta (prevod originalnog zadatka), sačuvan kao
referenca za namjeru i duh projekta. NIJE operativni plan rada.

Operativni dokumenti koje Claude Code prati su: ROADMAP.md (redoslijed
faza), CLAUDE.md (pravila razvoja i konkretni interfejsi), DATA_MODEL.md
(šema podataka). Ovaj fajl se konsultuje samo kad ta tri dokumenta ne daju
jasan odgovor na neko pitanje (vidi CLAUDE.md, posljednja tačka).
-->

# Home OS — Zadatak za aplikaciju

Napravite lični "kućni operativni sistem": jednu aplikaciju koja objedinjuje
sve svakodnevne administrativne obaveze domaćinstva na jednom mjestu,
dijeljenu između članova domaćinstva, uz email obavještenja koja svima
omogućavaju da budu u toku.

Poenta je da je sve povezano — račun može kreirati zadatak, zadatak se može
pojaviti u kalendaru, podsjetnik može doći odakle god — tako da to djeluje
kao jedna aplikacija, a ne kao fascikla punih odvojenih aplikacija.

---

## Aplikacija treba da uključuje

### Kontrolna tabla (Dashboard)

- Početni ekran "Danas" koji objedinjuje ono što je trenutno bitno: zadatke
  s rokom, današnje događaje, predstojeće račune i aktivne podsjetnike.
- Brzo dodavanje — dodajte zadatak, bilješku ili podsjetnik odakle god, bez
  pretraživanja menija.
- Pretraga kroz sav sadržaj na jednom mjestu.

### Zadaci

- Kreiranje i upravljanje zadacima s rokovima, prioritetima i odgovornom
  osobom.
- Podzadaci, oznake (tagovi) i ponavljajući zadaci.
- Označavanje kao završeno i pregled zakašnjelih zadataka.

### Kanban

Pregled zadataka u obliku table, organizovanih u kolone (npr. Za uraditi /
U toku / Završeno).

- Prevlačenje kartica između kolona.
- Više tabli za različita područja domaćinstva.

### Kalendar

- Mjesečni, sedmični i dnevni prikaz događaja.
- Zadaci s rokovima se automatski prikazuju u kalendaru.
- Pregled svih zajedničkih događaja domaćinstva na jednom mjestu.

### Podsjetnici

- Jednokratni i ponavljajući podsjetnici, namijenjeni određenim članovima.
- Podsjetnici se mogu pokrenuti odakle god u aplikaciji (račun, datum
  obnove, zadatak).
- Obavještenja unutar aplikacije i putem emaila.

### Bilješke

- Jednostavne bilješke s oznakama.
- Prostor za dnevne bilješke / dnevnik.
- Povezivanje bilješke sa srodnim zadatkom, računom ili događajem.

### Finansije

- Praćenje troškova i prihoda po kategorijama, s budžetima.
- Upravljanje pretplatama i ponavljajućim računima s rokovima plaćanja.
- Obavještenje prije dospijeća računa.
- Mjesečni pregled, te jednostavan uvid u to ko je platio, a ko duguje.

### Administracija domaćinstva (Life admin)

- Mjesto za evidenciju domaćinstva: dokumenti, garancije, obnove i važni
  kontakti.
- Datumi obnove i isteka koji automatski pokreću podsjetnike.
- Zajedničke liste za kupovinu i kućanske poslove.

---

## Dijeljeno unutar cijelog domaćinstva

### Dijeljenje s članovima domaćinstva

- Svaki član domaćinstva može biti dodan kao član.
- Odabir šta je privatno, šta je dijeljeno sa cijelim domaćinstvom, a šta s
  određenim osobama.
- Dodjeljivanje zadataka i podsjetnika članovima i uvid u to ko je
  odgovoran.
- Izmjene koje napravi jedan član vidljive su svima.

### Email obavještenja

Emailovi za stvari koje su bitne: aktiviranje podsjetnika, zadatak koji vam
je dodijeljen, dospijeće računa, nešto što je podijeljeno s vama.

- Svaki član sam bira koje od ovih obavještenja želi primati putem emaila.
- Opcionalni dnevni ili sedmični sažetak (digest) s pregledom predstojećih
  obaveza.
- Jednostavno uključivanje ili isključivanje bilo koje kategorije.

---

## Proširivost — izgradnja novih aplikacija

Cijeli sistem je platforma. Osam gore navedenih aplikacija su samo prve
instalirane na njoj; svako — ili bilo koji agent — trebao bi moći izgraditi
novu aplikaciju koja se uklapa i odmah funkcioniše zajedno s ostalima. Ovo
su principi kojima se treba voditi način na koji funkcioniše proširivost.

### Nove aplikacije su ravnopravni članovi sistema

- Nova aplikacija se instalira na isti način kao i ugrađene, bez posebnih
  izuzetaka i bez izmjena postojećih aplikacija.
- Nakon instalacije, pojavljuje se svuda gdje se pojavljuju i ugrađene
  aplikacije — na kontrolnoj tabli, u pretrazi, u komandnoj paleti, u
  navigaciji.
- Instaliranje i uklanjanje aplikacije treba biti čisto i reverzibilno, bez
  ostavljanja bilo čega pokvarenog.

### Gradite na postojećem, ne duplirajte ga

- Nova aplikacija treba moći čitati i ponovo koristiti postojeće podatke,
  umjesto da ih iznova kreira. Planer obroka ne bi trebao izmišljati vlastiti
  sistem zadataka — trebao bi koristiti postojeći.
- Nova aplikacija može povezati vlastite elemente s postojećima, tako da
  njeni objekti žive u istoj povezanoj mreži kao i sve ostalo.
- Zajedničke funkcionalnosti — podsjetnici, obavještenja, email, dijeljenje
  i članovi domaćinstva — obezbjeđuje platforma samo jednom. Nove aplikacije
  se oslanjaju na njih umjesto da ih iznova grade.

### Aplikacije sarađuju bez međusobnog poznavanja

- Aplikacije najavljuju šta se dešava unutar njih i reaguju na ono što se
  dešava drugdje, tako da mogu sarađivati bez direktnog međusobnog
  povezivanja.
- Ovo održava sistem otvorenim: nova aplikacija se može nadograditi na
  ugrađene, a buduća aplikacija se može nadograditi na nju, bez potrebe da
  iko to unaprijed planira.
- Sve što nova aplikacija uvede treba također biti dostupno automatizacijama
  domaćinstva, kako bi korisnici to mogli uklopiti u vlastita "kada se ovo
  desi, uradi ono" pravila.

### Postojeće aplikacije moraju biti dobri članovi platforme

- Od svake ugrađene aplikacije se očekuje da svoje podatke, radnje i
  značajne trenutke učini dostupnim drugim aplikacijama — mogućnost
  proširivanja je dio posla svake aplikacije, a ne naknadna dopuna.
- Ono što aplikacija izlaže je obećanje na koje se drugi oslanjaju. Ne bi se
  trebalo mijenjati na način koji tiho narušava aplikacije izgrađene na
  tome.
- Aplikacije trebaju pretpostaviti da neće uvijek biti prisutne. Nova
  aplikacija bi se trebala ponašati razumno čak i ako nešto na šta se
  oslanjala nije instalirano.

### Domaćinstvo zadržava kontrolu

- Nova aplikacija dobija pristup samo onim podacima i mogućnostima koje joj
  je domaćinstvo odobrilo.
- Pristup se dodjeljuje namjerno i može se preispitati ili opozvati, tako da
  proširivanje sistema nikada tiho ne proširuje ono što neko može vidjeti
  ili uraditi.
- Nove aplikacije poštuju ista pravila privatnosti i dijeljenja kao i sve
  ostalo — proširivost nikada ne postaje način zaobilaženja tih pravila.

### Smjernice za agenta koji gradi novu aplikaciju

- Krenite od onoga što domaćinstvo već ima: ponovo iskoristite postojeće
  podatke i mogućnosti prije nego dodate nešto novo.
- Učinite novu aplikaciju vidljivom i upotrebljivom kroz zajedničke
  površine (kontrolna tabla, pretraga, paleta) kako bi od prvog dana
  djelovala prirodno.
- Omogućite joj da učestvuje u povezanoj mreži — da najavljuje šta radi,
  reaguje na ono što rade druge aplikacije i povezuje svoje objekte s
  postojećima.
- Tražite samo pristup koji joj je zaista potreban i osigurajte da se
  elegantno ponaša kada nešto na šta se oslanja nedostaje.

---

## Vodeći principi

- Sve je povezano — moduli trebaju komunicirati jedni s drugima, a ne
  stajati u odvojenim silosima.
- Brzo dodavanje stvari; niska frikcija je ono što čini da se sistem
  zaista koristi.
- Domaćinstvo je na prvom mjestu: dijeljeno po defaultu tamo gdje ima
  smisla, privatno kada treba biti.
