# Home OS — Roadmap projekta (od nule do deploymenta)

Ovaj dokument definiše faze izrade "Home OS" platforme, redoslijed rada i
kriterije završenosti (definition of done) za svaku fazu. Namijenjen je kao
plan rada za Claude Code — svaka faza treba biti završena, testirana i
commitovana prije prelaska na sljedeću.

Prateći dokumenti: `CLAUDE.md` (pravila razvoja), `DATA_MODEL.md` (šema
podataka), `docs/ORIGINAL_SPEC.md` (izvorni brief — referenca za namjeru i
duh zadatka, ne operativni plan; kod nejasnoće koju ova tri dokumenta ne
razrješavaju, provjeriti tamo prije donošenja odluke).

---

## Rok: 5 radnih dana (uključujući vikend) — pun obim se zadržava

Zadatak primljen srijeda 16h, rok predaje ponedjeljak 16h. Rad uključuje i
vikend (subota, nedjelja) — **pet dana ukupno**: četvrtak, petak, subota,
nedjelja, ponedjeljak. Uz ovo produženje, pun opseg svih 11 faza ostaje
cilj — nema unaprijed isključenih modula. Ipak, raspored po danima ispod
postoji kao **prioritetni redoslijed**, ne kao opcioni plan: ako dođe do
nepredviđenog kašnjenja (npr. tehnički problem sa serverom), redoslijed
određuje šta se radi prvo, tako da ono što najviše nosi ocjenu
(povezanost, proširivost, deployment) ostane sigurno, čak i ako nešto s
kraja liste (npr. dio Faze 9 polish-a) mora biti skraćeno u zadnji čas.

**Raspored po danima (orijentacioni, prilagoditi po stvarnom napretku):**

| Dan | Fokus |
|---|---|
| Četvrtak (Dan 1) | Faza 0 + Faza 0.5 (deploy sa postojećim wildcard SSL sertifikatom — vidi izmjenu niže), pa start Faze 1 |
| Petak (Dan 2) | Dovršiti Fazu 1, Faza 2 (dashboard + dizajn token sistem) |
| Subota (Dan 3) | Faza 3 (Zadaci + Kanban + Kalendar) — najvažniji funkcionalni dan, glavni dokaz "sve je povezano" |
| Nedjelja (Dan 4) | Faza 4 (Podsjetnici + Bilješke), Faza 5 (Finansije + Life admin) |
| Ponedjeljak (Dan 5, do 16h) | Faza 6 (dijeljenje + email), Faza 7 (extensibility + skill fajl + dummy app dokaz), minimalna Faza 8, kratka Faza 9, finalizacija `SUBMISSION.md`, buffer prije predaje |

**Napomena o Danu 5:** ovo je najgušći dan (4 faze + finalizacija) — ako
Dani 1-4 idu po planu, ostaviti minimalno 1-2 sata čistog buffer-a prije
16h za nepredviđeno (deploy problem u zadnji čas je gore od skraćene Faze
9). Ako se bilo koji raniji dan produži, prva stvar koja se skraćuje na
Danu 5 je Faza 9 (polish) — nikad Faza 7 (extensibility dokaz) ili sam
deployment.

---

## Napomena: prilagodba Faze 0.5 — Apache/Virtualmin umjesto Caddy

Provjera servera (`ss -tlnp`, `apachectl -S`, `virtualmin list-domains`)
pokazala je da server već hostuje desetine domena kroz **Apache pod
Virtualmin-om**, koji drži javne portove 80/443 za sve njih. Zbog toga se
originalni plan (Caddy kao SSL/reverse-proxy sloj unutar Docker stacka)
NE koristi — Caddy se u potpunosti uklanja iz stacka. Umjesto toga (vidi
`CLAUDE.md` tačku 3a za potpunu arhitekturu):

- `homeos.imel.cloud` se kreira kao novi Virtualmin virtual server (ručno,
  vidi Preduslove niže) — Virtualmin/Apache drži SSL sertifikat i javne
  portove za tu domenu, isto kao za sve ostale na serveru.
- Docker stack (Nginx + PHP-FPM + ostalo) izlaže **samo interni port** na
  `127.0.0.1`, npr. `127.0.0.1:8091:80` — nikad na `0.0.0.0`. Tačan broj
  porta se bira nakon provjere da nije zauzet.
- Apache vhost za `homeos.imel.cloud` je konfigurisan kao reverse proxy ka
  tom internom portu, dodano kroz Virtualmin "Edit Directives" (ne ručnim
  editovanjem `.conf` fajla — Virtualmin ga može regenerisati).
- SSL/TLS se u potpunosti rješava na Apache/Virtualmin nivou — Docker
  stack nema nikakvu TLS konfiguraciju niti pristup cert/key fajlovima.

Ovo je jednostavnije od originalnog Caddy plana (jedan manje kontejner,
nema dupliranja SSL logike) i sigurnije za dijeljeni server (Apache i
dalje ima punu kontrolu nad 80/443 za sve postojeće sajtove).

---

## Preduslovi prije Faze 0 (ručni koraci, izvan Claude Code)

Ovo su infrastrukturni koraci koje radi čovjek (ne Claude Code) — Faza 0 i
0.5 pretpostavljaju da su gotovi prije nego agent počne. Uraditi što prije
zbog DNS propagacije i mogućih instalacionih problema:

**Večeras (prije Dana 1):**
- [x] **Docker Desktop instaliran** na Windows laptopu (WSL2 backend) —
  potrebno za lokalno pokretanje/testiranje `docker compose` prije deploya.
- [x] **Resend nalog kreiran**, domen dodat, SPF/DKIM DNS zapisi uneseni —
  raditi večeras zbog propagacije, ne sutra ujutro uz ostatak DNS-a.
- [x] **GitHub repo kreiran**, Claude Code ima pristup. (Provjeriti
  vidljivost — repo trenutno javan, sadrži nazive internih domena firme;
  odlučiti da li ostaje javan ili se prebacuje na privatan.)

**Sutra ujutro (prije Dana 1, ili kao prvi zadatak Dana 1):**
- [x] **Docker instaliran na Contabo serveru** (Ubuntu 24.04, zvanični
  Docker CE repo), potvrđeno `docker run hello-world`.
- [x] Virtualmin virtual server za `homeos.imel.cloud` kreiran, SSL
  sertifikat postavljen kroz Virtualmin.
- [x] Apache proxy moduli (`proxy_module`, `proxy_http_module`) potvrđeni
  aktivni — nije trebala dodatna izmjena.
- [x] Izolovan MySQL korisnik (`homeos`) i baza (`homeosdb`) kreirani,
  pristup ograničen samo na tu bazu (vidi `DATA_MODEL.md` napomenu o
  produkciji) — **provjeriti** `SHOW GRANTS FOR 'homeos'@'localhost';`
  da su prava zaista ograničena na `homeos.*`, ne `*.*`.
- [x] Probni `index.html` dostupan na `homeos.imel.cloud` portu 80 i 443
  (potvrđeno — trenutno Virtualmin default placeholder stranica, što je
  dovoljno da se potvrdi da DNS/SSL/webserver rade)
- [x] Provjeriti koji interni (loopback) portovi su već zauzeti na serveru
  (`sudo ss -tlnp | grep 127.0.0.1`) — potvrđeno: **port 8091 slobodan**,
  koristi se za Docker app stack. Usput potvrđeno: postojeći MariaDB već
  sluša na `127.0.0.1:3306` (isti proces na kom je kreirana `homeos` baza)
  — produkcija se na njega povezuje direktno, bez zasebnog MySQL
  kontejnera (vidi `CLAUDE.md` tačku 3a i `DATA_MODEL.md`).

Kad je ova lista gotova, Faza 0.5 se svodi na povezivanje ovih već
postojećih komada (Docker stack na internom portu + Apache reverse proxy
direktiva u Virtualmin-u + `deploy.yml`) — ne na njihovo prvo kreiranje,
što je dodatna ušteda vremena.

---

## Faza 0 — Priprema i temelji (scaffolding)

**Cilj:** Prazan, ali potpuno funkcionalan skeleton projekta koji se može
pokrenuti lokalno i deployati, prije nego što se doda ijedna "app" (Zadaci,
Kalendar, itd.).

1. Kreirati Git repozitorij (GitHub, privatni).
2. Laravel projekt (najnovija LTS verzija) + inicijalni commit.
3. Docker okruženje:
   - `Dockerfile` (PHP-FPM + potrebne ekstenzije)
   - `docker-compose.yml`: `app`, `caddy`, `mysql`, `redis`, `queue-worker`,
     `scheduler`
   - `docker-compose.override.yml` za lokalni dev (xdebug, mailhog/mailpit
     za testiranje emaila)
4. `.env.example` sa svim potrebnim varijablama dokumentovanim (bez pravih
   vrijednosti).
5. Osnovna autentifikacija (Laravel Breeze/Fortify) — login, registracija,
   reset lozinke.
6. Model `Household` (domaćinstvo) i `HouseholdMember` (član domaćinstva) —
   temelj za "shared across the whole household".
7. GitHub Actions workflow: lint + testovi na svaki push/PR (bez deploya još).
8. README.md sa uputama za lokalno pokretanje (`docker compose up`).
9. Custom Filament tema — Tailwind theme scaffold uključen u Docker build
   (`npm run build` korak), čak i sa privremenom paletom — stvarni token
   sistem (boje, tipografija) definiše se prije Faze 2, ali build-pipeline
   za temu mora postojati od početka (vidi `CLAUDE.md` tačku 6).

**Definition of done:** `docker compose up` lokalno pokreće aplikaciju,
korisnik se može registrovati, kreirati domaćinstvo i pozvati člana. CI
prolazi zeleno.

---

## Faza 0.5 — Probni deploy skeletona (rano, prije platform jezgra)

**Cilj:** Potvrditi da cijeli deployment lanac radi dok je aplikacija još
prazna — otkriti probleme sa serverom, portovima, SSL-om ili CI/CD
pristupom sada, ne nakon što je sve izgrađeno.

1. Domena: **`homeos.imel.cloud`** — Virtualmin virtual server + DNS
   (urađeno ručno, vidi Preduslove iznad).
2. **Resend domain verifikacija** — dodati domen u Resend dashboardu i
   podesiti SPF/DKIM DNS zapise (vidi `CLAUDE.md` tačku 3) — bez ovoga
   se email notifikacije neće moći pouzdano testirati u produkciji.
3. Produkcijski `docker-compose.prod.yml` — Nginx (interni, bez SSL) +
   PHP-FPM + Redis + queue-worker + scheduler, sa restart policy i
   resource limits. **Bez MySQL kontejnera** — baza je već postojeći
   MariaDB na hostu (`homeosdb` baza, `homeos` korisnik, potvrđeno na
   `127.0.0.1:3306`); app kontejner se povezuje preko
   `host.docker.internal` (`extra_hosts: host.docker.internal:
   host-gateway`). Nginx servis mapiran isključivo na `127.0.0.1:8091`
   (port potvrđen slobodan, vidi Preduslove) — nikad na javni port, jer
   Apache/Virtualmin već drži 80/443 za sve domene na serveru.
4. Apache reverse proxy direktiva za `homeos.imel.cloud` dodana kroz
   Virtualmin "Edit Directives" (`ProxyPass`/`ProxyPassReverse` ka
   `http://127.0.0.1:8091/`) — ovo je jedini korak koji dira postojeću
   Virtualmin konfiguraciju, i radi se pažljivo/ručno, ne automatizovano.
5. GitHub Actions `deploy.yml` (osnovna verzija):
   - build → SSH na Contabo server → `git pull` →
     `docker compose -f docker-compose.prod.yml up -d --build` →
     `php artisan migrate --force`
   - health-check nakon deploya (na interni port, ne na javni URL, da
     health-check ne zavisi od Apache reverse proxy sloja)
6. GitHub Secrets podešeni: `DEPLOY_SSH_HOST`, `DEPLOY_SSH_USER`,
   `DEPLOY_SSH_KEY`, `DEPLOY_PATH`, `RESEND_KEY`.
7. Provjera: login stranica (iz Faze 0) dostupna na
   `https://homeos.imel.cloud`, push na `main` automatski redeploya.

**Definition of done:** `https://homeos.imel.cloud` prikazuje login
stranicu praznog skeletona (SSL sertifikat i dalje važeći, sad izdat kroz
Virtualmin), i push na `main` grani automatski redeploya promjenu u roku
od par minuta — bez ikakvog uticaja na ostale domene/servise na serveru.

Ovo poglavlje je preduslov za Fazu 1 — dok deploy lanac ne radi pouzdano,
nema smisla graditi platform jezgro na koji bi se tek kasnije "prvi put"
pokušao deploy.

---

## Faza 1 — Platform jezgro (event bus, notifikacije, dijeljenje)

Ovo je najvažnija faza — sve buduće "app" module (Zadaci, Kalendar, Finansije...)
grade se na ovome. Greška ovdje se ponavlja u svakom modulu koji dođe kasnije.

1. **Event/Listener konvencija** — generički mehanizam kojim bilo koji modul
   može "najaviti" šta se desilo (npr. `TaskCompleted`, `BillDueSoon`) a da
   drugi moduli mogu slušati bez direktne zavisnosti. Vidi `CLAUDE.md` →
   "Event-driven pravilo".
2. **Notification sistem** — Laravel Notifications sa dva kanala: `mail` i
   `database` (in-app). Svaki `HouseholdMember` ima podešavanja koje
   kategorije obavještenja želi primati emailom (preference model).
3. **Sharing/permissions model** — generički `Shareable` trait/mehanizam:
   svaki objekat (zadatak, bilješka, događaj...) može biti privatan, dijeljen
   sa cijelim domaćinstvom, ili sa određenim članovima. Ovo se gradi JEDNOM
   ovdje, ne ponovo u svakom modulu.
4. **Scheduler skeleton** — Laravel Scheduler konfigurisan i deployan (cron
   unutar `scheduler` kontejnera), spreman da moduli u njega registruju
   svoje periodične zadatke (podsjetnici, provjera računa koji dospijevaju).
5. **Command palette / global search skeleton** — osnovna infrastruktura
   (npr. Laravel Scout ili jednostavan query-based search) na koju će se
   svaki modul "prijaviti" sa svojim tipom sadržaja.
6. Testovi za sve gore navedeno.

**Definition of done:** Postoji dokumentovan i testiran način da bilo koji
budući modul: (a) emituje event, (b) šalje notifikaciju, (c) označi objekat
kao dijeljen/privatan, (d) registruje periodični zadatak, (e) postane
pretraživ — bez izmjene postojećeg koda.

**Naknadna dopuna (nakon Faze 3):** search infrastruktura (tačka 5) je u Fazi 1
imala samo backend (`SearchProviderContract` + `SearchService`), bez UI-ja —
pa se ništa nije moglo stvarno pretraživati kroz aplikaciju. To je bio propust
(DoD (e) i "command palette / global search" iz tačke 5 podrazumijevaju i ulaz
za korisnika).

Dodana je univerzalna pretraga kao **command palette** (Ctrl/Cmd+K,
`App\Platform\Filament\CommandPalette`) u topbaru — modal sa zatamnjenjem i
rezultatima grupisanim po aplikaciji, agregira sve registrovane providere preko
`SearchService`-a (bez izmjene koda modula). Ispred hamburgera na tabletu/mobilnom.

*419 na `/livewire/update` (riješeno):* custom Livewire komponenta u render hooku
ne prolazi Filamentov serving lifecycle na update-u, pa "current panel"/tenant
nisu bili postavljeni → `TaskResource::getUrl()` je bacao `TypeError` koji
Livewire u produkciji (app.debug=false) pretvara u tihi 419. Rješenje: komponenta
u `boot()` (izvršava se na svakom zahtjevu) eksplicitno postavlja Filament panel i
tenant, uz odbranu od null korisnika. Livewire testovi nisu hvatali ovo jer
`Livewire::test` sam uspostavi puni Filament kontekst.

---

## Faza 2 — Dashboard (prazan kontejner)

1. **Dizajn token sistem** — zaključati paletu (4-6 hex boja), tipografiju
   i signature element dashboard-a (vidi `CLAUDE.md` tačku 6), primijeniti
   na Filament temu iz Faze 0.
2. Ruta `/` sa "Today" prikazom.
3. Widget sistem — dashboard čita podatke iz drugih modula putem
   definisanog interfejsa (svaki modul izlaže "šta je bitno danas"), a ne
   direktnim upitima u tuđe tabele.
4. Quick capture komponenta (modal dostupan sa bilo koje stranice).

Ova faza ostaje "prazna" (bez pravih widgeta) dok se ne dodaju moduli u
Fazi 3+ — dashboard je svjesno napravljen da prikazuje ništa dok nema šta
da agregira. Ovo potvrđuje da je widget-interfejs ispravno dizajniran.

**Naknadna ispravka (QA, prije Faze 5):** "Brzo dodaj" je prvo bilo izvedeno kao
dropdown linkova koji navigiraju na create stranicu — to gubi kontekst i krši
namjeru iz ORIGINAL_SPEC ("dodaj … odakle god, bez pretraživanja menija", niska
frikcija) i sam opis iz tačke 4 ("modal dostupan sa bilo koje stranice").
Rekonstruisano u **modal** (Alpine + fetch POST, zamagljena pozadina kao command
palette): korisnik doda minimalne podatke, snimi zatvara modal i ostavlja ga na
trenutnoj stranici. Registry-driven: modul u `quick_capture` registruje `fields`
+ `handler` (`QuickCreateContract`); generički `QuickCreateController` (ruta
panela `/brzo/{key}`) validira i kreira. Bez Livewire (izbjegava 419 iz Faze 3 QA).

**Definition of done:** Dashboard se renderuje bez grešaka i sa 0 modula
instaliranih, sa primijenjenom custom temom (ne default Filament izgled),
provjeren vizuelno na mobile/tablet/desktop širinama.

---

## Faza 3 — Zadaci, Kanban, Kalendar

Ovi moduli se grade zajedno jer dijele isti osnovni entitet (Task).

1. Modul **Zadaci**: CRUD, rokovi, prioriteti, odgovorna osoba, podzadaci,
   oznake, ponavljajući zadaci (koristi Scheduler iz Faze 1).
2. Modul **Kanban**: view sloj nad istim Task modelom — kolone, boards,
   drag & drop (Livewire/Alpine.js ili Filament board widget).
3. Modul **Kalendar**: mjesečni/sedmični/dnevni prikaz. Zadaci s rokom se
   AUTOMATSKI pojavljuju ovdje putem event listenera na `TaskCreated` /
   `TaskDueDateChanged` — kalendar ne duplira task podatke.
4. Svaki modul se prijavljuje na dashboard widget interfejs iz Faze 2.
5. Testovi.
6. **Izdvojiti reusable skill za nove module** — nakon što modul Zadaci
   prođe kompletan checklist iz `CLAUDE.md` tačke 14, Claude Code izdvaja
   obrazac tog modula (Model, Migration, Policy, Filament Resource,
   DashboardWidget, SearchProvider stub-ovi + koraci registracije) u
   `.claude/skills/homeos-new-module/SKILL.md`, verzionisano u repou. Svaki
   naredni modul (Faza 4+) se gradi koristeći taj skill kao polazište, ne
   pisanjem svakog fajla od nule. Skill se dorađuje ako se u kasnijim
   fazama otkrije obrazac koji prvi skill nije predvidio (npr. polymorphic
   veza kao u Bilješkama).

**Definition of done:** Kreiranje zadatka s rokom automatski: (1) prikazuje
se na dashboardu, (2) pojavljuje se u kalendaru, (3) vidljivo je na kanban
tabli — bez ručnog povezivanja u svakom modulu. Skill fajl postoji u repou
i naredni modul ga stvarno koristi kao polaznu tačku.

**Napomene uz realizaciju Faze 3 (svjesne odluke, ne tiha improvizacija):**

- *Kalendar — pull agregacija umjesto listener push.* Tačka 3 gore je
  predviđala da kalendar "sluša" `TaskCreated`/`TaskDueDateChanged` i tako
  sazna za zadatke. Umjesto toga uveden je `CalendarSourceContract`
  (`app/Platform`): kalendar pri renderu POVLAČI događaje iz svih registrovanih
  izvora (`config/homeos-apps.php` → `calendar_source`). Rezultat je jači nego
  push varijanta — nema NIKAKVOG dupliranja task podataka (kalendar čita živ
  Task, ne kopiju), a Kalendar i dalje ne zna za Tasks. Zadaci ipak emituju te
  evente (za druge buduće slušatelje). DoD je ispunjen identično.
- *FullCalendar — self-hosted umjesto community Filament plugina.* Planirani
  `saade/filament-fullcalendar` podržava samo Laravel ≤12, a projekt je na
  Laravel 13 (Composer odbija instalaciju). Uz odobrenje vlasnika, FullCalendar
  v6 je ugrađen direktno preko npm-a i bundlan Viteom (`resources/js/calendar.js`),
  a hrani se istim `CalendarService` agregiranim događajima. Ista UX (mjesec/
  sedmica/lista, bosanski locale), bez nekompatibilne zavisnosti.

---

## Faza 4 — Podsjetnici i Bilješke

1. Modul **Podsjetnici**: jednokratni/ponavljajući, **namijenjeni određenim
   članovima** (odgovorna osoba — ORIGINAL_SPEC), mogu biti pokrenuti iz bilo
   kojeg drugog modula (generički event mehanizam — npr. bill iz Finansija
   emituje event na koji se Podsjetnik "zakači").
2. Modul **Bilješke**: jednostavne bilješke + tagovi + dnevni journal +
   polymorphic veza ka bilo kojem drugom objektu (zadatak, račun, događaj).

**Definition of done:** Podsjetnik se može kreirati vezan za bilo koji
postojeći entitet (task, bill), i on to čini kroz javni interfejs tog
entiteta, ne kroz direktan pristup njegovoj bazi.

**Naknadna ispravka (QA):** prva realizacija je izostavila da je podsjetnik
"namijenjen određenim članovima" (ORIGINAL_SPEC, "Podsjetnici" + "Dijeljenje").
Dodano: `reminders_reminders.assigned_to` (član), izbor odgovorne osobe u formi,
a scheduler obavještava dodijeljenog člana (fallback kreator). `ReminderRequested`
event nosi opcioni `assignedTo`, pa podsjetnik kreiran s zadatka nasljeđuje
odgovornu osobu zadatka.

---

## Faza 5 — Finansije i Administracija domaćinstva (Life admin)

1. Modul **Finansije**: troškovi/prihodi po kategoriji, budžeti, pretplate i
   ponavljajući računi, mjesečni pregled, "ko je platio / ko duguje".
   Računi koji dospijevaju emituju event → Podsjetnici i Notifikacije to
   automatski hvataju (iz Faze 1 i 4) — nema novog koda za to u ovom modulu.
2. Modul **Life admin**: evidencija dokumenata/garancija/kontakata, datumi
   isteka → isti event mehanizam za automatske podsjetnike. Zajedničke liste
   za kupovinu.

**Definition of done:** Kreiranje računa s datumom dospijeća automatski
generiše podsjetnik i email obavještenje bez ijedne linije koda van modula
Finansije.

---

## Faza 6 — Dijeljenje, email obavještenja, digest

1. UI za upravljanje članovima domaćinstva i njihovim dozvolama.
2. UI za granularno biranje šta je privatno/dijeljeno po objektu (koristi
   Sharing model iz Faze 1).
3. Podešavanja obavještenja po članu — uključi/isključi kategorije.
4. Dnevni/sedmični digest email (Scheduler job koji agregira sve module).

**Definition of done:** Član domaćinstva može isključiti sve emailove osim
"bill coming due" i to se poštuje sistemski.

---

## Faza 7 — Extensibility layer (platforma za buduće apps)

Ovo formalizuje ono što je već implicitno urađeno kroz Faze 1-6.

1. App registry — mehanizam kojim se modul "registruje" u sistem (naziv,
   ikonica, dashboard widget, search provider, meni stavka) kroz
   konfiguraciju, ne hardkodovanjem u core-u.
2. Dokumentovan checklist za dodavanje nove app (vidi `CLAUDE.md`).
3. Graceful degradation — testirati da sistem radi i kada je opcioni modul
   isključen (npr. ugasiti Finansije i provjeriti da ništa ne puca).
4. Access/permission scoping po modulu (household odlučuje šta modul smije
   vidjeti).

**Definition of done:** Nova probna "dummy" app se doda prateći checklist i
pojavi se na dashboardu/search-u/navigaciji bez izmjene postojećeg koda.

---

## Faza 8 — Deployment pipeline (produkcijsko dovršavanje)

Osnovni lanac (`homeos.imel.cloud`, Apache/Virtualmin SSL + reverse proxy,
`deploy.yml`, izolacija od
ostalih servisa) je već uspostavljen i provjeren u Fazi 0.5. Ova faza ga
dovodi do potpune produkcijske spremnosti sada kad je sistem funkcionalan
i sadrži prave podatke.

1. Backup strategija — dnevni MySQL dump (cron), rotacija starijih backupa,
   opcionalno upload na eksterni storage.
2. Monitoring — jednostavan uptime/log monitoring (npr. Laravel log +
   health-check endpoint; opcionalno besplatni eksterni uptime monitor).
3. Rollback provjera — potvrditi da `git revert` + re-deploy stvarno vraća
   prethodnu verziju bez gubitka podataka (probni rollback na test grani).
4. Resource limits/health checks revidirani sad kad se zna stvarno
   opterećenje (queue-worker, scheduler, Reverb).
5. Ponovna provjera izolacije od ostalih servisa na serveru sad kad su svi
   moduli aktivni (portovi, resursi, MySQL user permissions).

**Definition of done:** Push na `main` grani automatski deploya novu verziju
na produkciju, dnevni backup radi, i probni rollback je uspješno testiran
bez gubitka podataka.

---

## Faza 9 — Polish i dokumentacija

1. Testno pokrivanje ključnih tokova (feature testovi po modulu).
2. Ažuriranje `README.md` i `CLAUDE.md` sa svim naučenim tokom razvoja.
3. Finalni UX prolaz — sistematska provjera svih ekrana protiv `CLAUDE.md`
   tačke 6 (vizuelni dizajn i responzivnost); ovo je posljednja provjera,
   ne prva primjena pravila — svaki modul je od svoje faze već trebao
   ispuniti ta pravila kroz checklistu (tačka 14).
4. Sigurnosni pregled — rate limiting, CSRF, autorizacija po household-u
   (da član jednog domaćinstva ne može vidjeti podatke drugog).

---

## Napomena o redoslijedu

Faze 0, 0.5 i 1 su neopozive i idu tim redoslijedom — Faza 0.5 mora
potvrditi da deploy lanac radi prije nego što se gradi platform jezgro, a
svaka sljedeća faza pretpostavlja da event/notification/sharing mehanizam
iz Faze 1 postoji i ispravno radi. Ako se tokom Faze 3+ otkrije da nešto
nedostaje u platform jezgru, popravka ide nazad u Fazu 1, ne "zakrpa" u
modulu koji je otkrio problem.
