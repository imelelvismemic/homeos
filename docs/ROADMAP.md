# Home OS — Roadmap projekta (od nule do deploymenta)

Ovaj dokument definiše faze izrade "Home OS" platforme, redoslijed rada i
kriterije završenosti (definition of done) za svaku fazu. Namijenjen je kao
plan rada za Claude Code — svaka faza treba biti završena, testirana i
commitovana prije prelaska na sljedeću.

Prateći dokumenti: `CLAUDE.md` (pravila razvoja), `DATA_MODEL.md` (šema
podataka), `ORIGINAL_SPEC.md` (izvorni brief — referenca za namjeru i
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

**Status:** Fazu 5 isporučujemo u dva koraka (odluka vlasnika, veliki moduli).
- **Faza 5a — Finansije: GOTOVO i deployano.** Transakcije (prihod/rashod) po
  kategorijama, budžeti po kategoriji/mjesecu, računi/pretplate (rok, ponavljanje,
  `remind_days_before`), mjesečni pregled + "ko duguje kome" (`BalanceService`).
  DoD ispunjen: na kreiranju računa Finance emituje `ReminderRequested` (X dana
  prije dospijeća) → Reminders kreira podsjetnik → scheduler okine → email; ništa
  van Finansija. Račun se pojavljuje i na kalendaru/dashboardu/pretrazi.
- **Faza 5b — Life admin: GOTOVO i deployano.** Jedinstven model Dokument (tip +
  datum isteka + privatni prilog/sken), zaseban model Kontakti, zajedničke liste za
  kupovinu (štikliranje). DoD ispunjen: dokument s datumom isteka emituje
  `ReminderRequested` (X dana ranije, default 30) → Podsjetnici → scheduler → email;
  ništa van Life admina. Dokument se pojavljuje i na kalendaru/dashboardu/pretrazi.
  Prilozi na privatnom disku `documents` (autentikovano preuzimanje, Policy), uz
  perzistentni Docker volumen `app-storage`. Kućanski poslovi idu kroz modul Zadaci
  (odluka vlasnika), bez dupliranja.

---

## Faza 6 — Dijeljenje, email obavještenja, digest

1. UI za upravljanje članovima domaćinstva i njihovim dozvolama.
2. UI za granularno biranje šta je privatno/dijeljeno po objektu (koristi
   Sharing model iz Faze 1).
3. Podešavanja obavještenja po članu — uključi/isključi kategorije.
4. Dnevni/sedmični digest email (Scheduler job koji agregira sve module).

**Definition of done:** Član domaćinstva može isključiti sve emailove osim
"bill coming due" i to se poštuje sistemski.

**Status: GOTOVO i deployano** (u dva koraka).
- **6a** — Zajednički "Podijeli" mehanizam (`App\Platform\Filament\Sharing`)
  na svih 8 Shareable entiteta (Privatno/Domaćinstvo/Određeni članovi); upravljanje
  članovima (`HouseholdMemberService`: promjena uloge, uklanjanje, prijenos
  vlasništva, uz invariantu "bar jedan vlasnik").
- **6b** — Postavke obavještenja po članu (`NotificationSettings`): email po
  kategoriji (registry `NotificationCategoryRegistry`) + ritam digesta. Digest email
  (`DigestService`/`DigestNotification`, mail-only) agregira module kroz
  `DigestSourceContract` (registry `digest_source`); dnevni/sedmični scheduler; polje
  `household_members.digest_frequency`. DoD ispunjen (član isključi sve osim `bill_due`).
  **In-app sanduče** (`NotificationsInbox`) + zvonce s brojačem nepročitanih —
  scope-ovano na trenutnog člana (obavještenja idu na `HouseholdMember`, pa native
  Filament zvonce ne bi radilo).
- **Privatnost izvedenih zapisa (ispravljeno):** izvedeni podsjetnik/bilješka/trošak
  nasljeđuje vidljivost izvora. Zajednički `App\Platform\Sharing\VisibilityMirror` +
  novi event `VisibilityChanged` (emituje ga Shareable na makePrivate/shareWith/
  shareWithHousehold); Reminders/Notes slušaju i usklađuju svoje izvedene zapise. Time
  privatan račun povlači privatan podsjetnik (naziv više ne curi domaćinstvu).

---

## QA prolaz kroz cijeli sistem (prije Faze 7)

Vlasnički pregled cijele aplikacije nakon Faze 6. Ispravljeno:

- **Vremenska zona** — `config/app.php` je imao hardkodovan `'UTC'` i ignorisao
  `APP_TIMEZONE`. Posljedica: pozdrav na dashboardu u 06:55 je glasio „Dobro veče“
  (04:55 UTC), a podsjetnici su okidali dva sata kasnije od upisanog vremena.
  Sada `env('APP_TIMEZONE', 'UTC')` → `Europe/Sarajevo`. Podaci se ne migriraju:
  vremena su i upisivana i prikazivana kao lokalna, pa im ovo tek daje ispravno
  značenje.
- **Podsjetnici — okidanje** — uzrok obavještenja „svake minute“ i cijela
  funkcionalnost dokumentovani u `DATA_MODEL.md` §4a. Uvedeno: `ReminderFirer`
  (jedan put za scheduler i UI), okidanje s liste, s forme podsjetnika i s
  dashboard widgeta, obavještenje i kod ručnog okidanja.
- **Kalendar** — klik na dan otvara „Brzo dodaj“ s postavljenim datumom (spec:
  „dodajte zadatak, bilješku ili podsjetnik odakle god“); 24-satni prikaz vremena
  u sedmičnom/dnevnom/list prikazu (RULES.md §6).
- **Bilješke** — dnevnik dobio stvarnu razradu: zasebna kartica „Dnevnik“ na
  listi, akcija „Dnevnik za danas“ (prefill datuma) i prikaz unosa dnevnika na
  kalendaru (`JournalCalendarSource`). Iz editora uklonjeno nefunkcionalno
  dugme za prilaganje fajlova (fajlovi idu kroz Administraciju → Dokumenti).
- **Profil korisnika** — dodan (`->profile()`), s promjenom lozinke koja traži
  potvrdu **trenutne** lozinke; prijevodi kroz `lang/vendor/filament-panels`.
- **Prilog dokumenta veći od 2 MB** — bazni `php:8.3-fpm-alpine` ne aktivira
  nijedan `php.ini`, pa su vrijedile ugrađene vrijednosti `upload_max_filesize=2M`
  / `post_max_size=8M`: PHP je odbacivao fajl, Livewire upload nikad ne bi završio
  i dugme „Sačuvaj“ je ostajalo zaglavljeno (manji fajlovi su prolazili). Dodan
  `docker/php.ini` (20M/24M) u runtime image; lanac limita je sada Filament 10 MB
  < Livewire 12 MB < PHP 20 MB < Nginx 32 MB, pa korisnik dobije poruku iz
  aplikacije umjesto tihog pada.
- **Sanduče obavještenja** — filter „sakrij pročitane“ (uključen po defaultu) i
  brojač na zvoncetu koji se osvježi odmah po označavanju pročitanim.
- **Članovi** — dugme „Pozovi“ vidi samo vlasnik domaćinstva.
- **Prijevodi/terminologija** — „Columns“ → „Kolone“ na svim listama (tačan
  paketski ključ, vidi RULES.md §1); ujednačeno „lozinka“ umjesto „šifra“ u
  cijelom sistemu; pretraga listi po odgovornoj osobi/oznakama fiksirana kao
  pravilo (RULES.md §8).

**Drugi krug QA-a (isti prolaz, nakon provjere vlasnika):**

- **Naziv domaćinstva** — vlasnik ga mijenja kroz „Postavke domaćinstva“
  (`EditHouseholdProfile`, Filament tenant profil); pristup ide kroz postojeću
  `HouseholdPolicy::update`, dakle član ne može.
- **Profil korisnika je vraćao 500** — Filamentov ugrađeni `->profile()` se
  registruje IZVAN tenant rute, pa panel layout puca kad navigacija zatraži
  URL-ove vezane za domaćinstvo. Zamijenjen vlastitom stranicom panela
  (`UserProfile`), dostupnom iz korisničkog menija. Uz to: **profilna slika**
  (upload i uklanjanje) na privatnom disku, servirana autentikovanom rutom
  (`AvatarController`) — javni disk nije opcija jer Nginx servira host checkout.
- **Kalendar** — događaji se sada dohvataju kao FullCalendar feed
  (`CalendarEventsController`, raspon koji je prikazan), pa se nakon brzog
  dodavanja osvježe **bez promjene mjeseca**. Izabrani dan se prosljeđuje unosu:
  zadatku kao rok, bilješci kao datum dnevnika, trošku kao datum (ranije je
  upisivao današnji), podsjetniku kao predloženo vrijeme.
- **Brzo dodavanje računa** — modul sada može registrovati **više** tipova
  brzog unosa (`quick_capture` kao lista definicija s vlastitim `key`), pa
  Finansije nude i trošak (`finance.expense`) i račun (`finance.bill`, s
  naslovom, iznosom i rokom). Dodan i tip polja `date`.
- **„Nazad“ s forme uređivanja** vodi na listu, ne na prethodnu stranicu
  (`CancelReturnsToList` na svim Edit stranicama) — `RULES.md` §9.
- **Mobilna navigacija** — meni je dobio rezervu na dnu (uz `safe-area`), jer je
  URL traka mobilnog browsera prekrivala posljednje stavke.

**Treći krug QA-a — konsolidacija postavki:**

- **Postavke obavještenja** više nisu zasebna stavka menija — postale su kartica
  na profilu korisnika (`UserProfile`: Nalog / Lozinka / Obavještenja), jer su i
  jedno i drugo „moje postavke“. Kategorije i dalje dolaze iz registryja.
- **Članovi domaćinstva** više nisu zasebna stavka menija ni Filament Resource —
  lista i sve radnje (pozovi, promijeni ulogu, prenesi vlasništvo, ukloni) su na
  stranici **Postavke domaćinstva**, uz naziv domaćinstva. **Svaki član vidi ko
  je u domaćinstvu**; izmjene (naziv i radnje nad članovima) ostaju vlasniku —
  zato `canView()` provjerava članstvo, a ne Policy `update`.
- **Profilna slika se nije prikazivala** nakon snimanja (bijeli kvadrat):
  privatni lokalni disk nema javni ni privremeni URL, pa je Filament generisao
  neupotrebljiv `/storage/...` link. Pregled sada ide kroz našu autentikovanu
  rutu (`getUploadedFileUsing`).
- **Prijevodi**: „Save changes“ na postavkama domaćinstva i cijeli uređivač slike
  (Cancel/Reset/Save/Width/Height/Rotation…) — kroz `lang/vendor/*` override, pa
  su spremni i za `en`/`de` iz Faze 9 (tačka 6).

**Četvrti krug QA-a — navigacija i mobilni prikaz:**

- **500 na kreiranju domaćinstva (produkcija)** — stavka „Moj profil“ u
  korisničkom meniju gradi URL s `{tenant}` parametrom, a Filament taj URL računa
  PRIJE provjere vidljivosti; na stranicama bez domaćinstva (kreiranje prvog
  domaćinstva) to je rušilo cijelu stranicu. URL se sada gradi samo kad tenant
  postoji. Pokriveno testom koji renderuje tu stranicu.
- **Kreiranje domaćinstva više nije stalna opcija** u padajućem meniju:
  `RegisterHousehold::canView()` je istinit samo dok korisnik nije ni u jednom
  domaćinstvu — time nestaje i stavka iz menija i direktan pristup URL-om, a
  Filamentovo preusmjeravanje nakon prijave i dalje pokriva slučaj „registrovao
  se pa zatvorio browser prije nego je dovršio kreiranje“. Namjerno „nije ni u
  jednom domaćinstvu“, a ne „nije vlasnik nijednog“: pozvani član nema svoje
  domaćinstvo, ali ima gdje raditi i ne smije biti tjeran da kreira vlastito.
- **Obavještenja** izbačena iz menija — zvonce u topbaru vodi na istu stranicu.
- **Redoslijed grupe „Organizacija“**: Zadaci, Podsjetnici, Bilješke, Kanban,
  Kalendar (eksplicitni `navigationSort`).
- **Univerzalna pretraga** sada nalazi i **članove domaćinstva** (samo trenutno
  odabranog). `MemberSearchProvider` je provider same platforme — registry u
  `config/homeos-apps.php` ostaje isključivo za module.
- **Mobilni**: bočni meni je koristio `h-screen` (100vh = viewport BEZ donje URL
  trake browsera), pa se do zadnjih stavki nije moglo doskrolovati — sada
  `100dvh`. Hamburger je prvi s lijeva, pa univerzalna pretraga (flex `order`,
  jer hook iza hamburgera ne postoji). Meni se zatvara na klik bilo kojeg linka,
  ne samo stavke menija (npr. „Postavke domaćinstva“ iz padajućeg menija).

**Nije propust, nego obim Faze 7:** uključivanje/isključivanje modula po
domaćinstvu. Ključ `enabled` u `config/homeos-apps.php` postoji i **svi** registri
ga već poštuju (dashboard, pretraga, kalendar, digest, brzo dodavanje, kategorije
obavještenja), ali je to zasad globalna postavka i navigacija Filamenta se i dalje
gradi auto-discoveryjem foldera — pa isključen modul nestane s dashboarda/pretrage,
a stavka u meniju ostane. Faza 7 to zaokružuje (tačke 1, 3 i 4 niže).

---

## Faza 7 — Extensibility layer (platforma za buduće apps)

Ovo formalizuje ono što je već implicitno urađeno kroz Faze 1-6.

**Status: isporučuje se u tri koraka** (odluka vlasnika, isti ritam kao Faze 5 i 6).

- **7a — GOTOVO:** app registry s uključenošću **po domaćinstvu**
  (`ModuleRegistry` + tabela `household_modules`, prekidači na stranici Postavke
  domaćinstva, vlasnik-only). Nijedan dio sistema više ne čita `enabled` iz
  configa direktno — dashboard, pretraga, kalendar, digest, brzo dodavanje i
  kategorije obavještenja idu kroz registry, a Filament Resource/Page kroz
  `BelongsToModule` nestaju i iz **menija** i s **rute**. Kalendar je dodan u
  registry (potrošač bez providera) da bi i on bio ugasiv. Graceful degradation
  pokriven testom sa **svim** modulima isključenim. Uz to: redoslijed grupa u
  meniju (Organizacija, Finansije, Administracija) i ispravka redoslijeda u
  topbaru na mobilnom (tačka 6 niže).
- **7b — GOTOVO:** probna app **Kućni ljubimci** (`app/Modules/Pets`) kao dokaz
  proširivosti. Ljubimac + termini njege (vakcina/pregled/terapija) s datumom;
  njega je RelationManager uz ljubimca, bez zasebne stavke u meniju. Uklopljen
  jednim unosom u `config/homeos-apps.php` — navigacija, dashboard, pretraga,
  kalendar, digest, „Brzo dodaj", dijeljenje i prekidač modula dolaze sami.
  Termin njege emituje platformski `ReminderRequested` → Podsjetnici kreiraju
  podsjetnik → email, bez importa tog modula. Namjerno koristi POSTOJEĆU grupu
  „Administracija“: nova grupa bi tražila dopunu `->navigationGroups([...])` u
  core provideru, pa bi dokaz „bez izmjene postojećeg koda“ bio slabiji.
  14 testova, uključujući rad kad su svi ostali moduli isključeni. Uz to:
  „Pozovi člana" premješteno uz listu članova i razdvojene dvije prazne poruke
  na početnoj strani.
- **7c — GOTOVO:** pozivnica putem linka (tačka 5 niže) + valuta kao postavka
  domaćinstva i kapitalizacija naslova riješena u osnovi (`ModuleResource`).
  Vlasnik unosi email bez obzira ima li osoba nalog: ako ima — odmah je član, ako
  nema — dobija email s jednokratnim linkom (vrijedi 7 dana, u bazi je samo hash
  tokena). Kroz link se registruje s **zaključanim** emailom i ulazi pravo u
  domaćinstvo, bez koraka „kreirajte svoje domaćinstvo“. Prihvatanje radi listener
  na Login eventu, pa isti put pokriva i onoga ko već ima nalog. Poslane pozivnice
  vlasnik vidi i može ih povući.

1. App registry — mehanizam kojim se modul "registruje" u sistem (naziv,
   ikonica, dashboard widget, search provider, meni stavka) kroz
   konfiguraciju, ne hardkodovanjem u core-u.
2. Dokumentovan checklist za dodavanje nove app (vidi `CLAUDE.md`).
3. Graceful degradation — testirati da sistem radi i kada je opcioni modul
   isključen (npr. ugasiti Finansije i provjeriti da ništa ne puca).
   **Uključuje i navigaciju:** isključen modul mora nestati i iz menija, ne samo
   s dashboarda/pretrage (vidi QA napomenu iznad).
4. Access/permission scoping po modulu (household odlučuje šta modul smije
   vidjeti) — uz to i UI kojim vlasnik domaćinstva pali/gasi module po sebi
   (`enabled` prelazi iz globalne konfiguracije u postavku po domaćinstvu).

   **Gdje ide taj UI:** na **postojeću stranicu „Postavke domaćinstva“**
   (`EditHouseholdProfile`), kao treća cjelina uz naziv domaćinstva i članove —
   ne kao nova stavka menija. Isti princip kao kod profila korisnika: sve što je
   „postavka domaćinstva“ živi na jednom mjestu. Lista modula se i tada čita iz
   `config/homeos-apps.php` (naziv + ikonica po modulu), a per-domaćinstvo stanje
   se čuva uz domaćinstvo; uključivanje/isključivanje je radnja vlasnika, član je
   samo vidi.

5. **Pozivnica putem linka** (dodano na zahtjev vlasnika, prije izrade faze).
   Danas vlasnik može pozvati samo **već registrovanog** korisnika, pa osoba
   koju treba pozvati mora prvo sama da se registruje — a registracija je
   obavezno vodi na kreiranje vlastitog domaćinstva (aplikacija je household-
   scoped, bez domaćinstva nema nijednog ekrana). Rezultat: pozvani član završi
   s jednim praznim domaćinstvom koje mu ne treba.

   Traženo ponašanje:
   - Vlasnik unese email, bez obzira je li osoba registrovana. Ako **jest** —
     dodaje se odmah (kao danas). Ako **nije** — šalje se pozivnica.
   - Pozivnica ide kroz Notification sistem (`CLAUDE.md` §10), nikad direktnim
     `Mail::send`; sadrži potpisan link s jednokratnim tokenom i rokom (7 dana).
   - Link vodi na registraciju s popunjenim emailom; nakon registracije korisnik
     **odmah ulazi u to domaćinstvo** s ulogom iz pozivnice i **ne prolazi** kroz
     formu kreiranja domaćinstva. Za već prijavljenog korisnika link samo
     prihvata pozivnicu.
   - Nova tabela `household_invitations` (household_id, email, role, token,
     expires_at, accepted_at, invited_by) — šema se upisuje u `DATA_MODEL.md`
     prije implementacije, po obrascu iz tačke 4 tog dokumenta.
   - Sigurnost: token jednokratan i istekne; poruka ne otkriva postoji li nalog
     s tim emailom; pozivnicu šalje i povlači samo vlasnik; prihvatanje provjerava
     da email pozivnice odgovara nalogu koji je prihvata.
   - Forma kreiranja domaćinstva time ostaje forsirana **samo** onome ko se
     registrovao sam od sebe, bez pozivnice — što je i bila namjera pravila iz
     QA prolaza prije ove faze.

6. **Dvije preostale sitnice iz QA-a** (vlasnik ih je svjesno odgodio za ovu
   fazu, jer su kozmetičke):
   - **Redoslijed u topbaru na mobilnom**: hamburger prvi s lijeva, pa
     univerzalna pretraga, pa ostalo redom. Prvi pokušaj (flex `order` u temi)
     nije proradio zbog **specifičnosti CSS-a**: pravilo `.fi-topbar > nav > *`
     (0,1,1) nadjačava `.homeos-command-palette` (0,1,0), pa su svi elementi
     ostali u istoj grupi. Ispravka je da selektori za hamburger i pretragu budu
     jednako specifični (`.fi-topbar > nav > .…`). Provjeriti na stvarnom
     uređaju, ne samo u emulaciji širine.
   - **Redoslijed grupa u meniju**: Organizacija, Finansije, Administracija.
     Filament grupe trenutno slaže redom kojim ih zatekne kroz auto-discovery
     modula; fiksira se s `->navigationGroups([...])` u `HomePanelProvider`,
     s istim prevedenim nazivima koje moduli vraćaju iz `getNavigationGroup()`
     (`lang/bs/<modul>.php` → `navigation_group`). Nova app dodaje svoju grupu
     u tu listu — spomenuti u checklisti iz `CLAUDE.md` §14.

**Definition of done:** Nova probna "dummy" app se doda prateći checklist i
pojavi se na dashboardu/search-u/navigaciji bez izmjene postojećeg koda.

**Ispunjeno** modulom Kućni ljubimci (7b) — vidi `SUBMISSION.md`, sekcija „Dokaz
proširivosti“. Faza 7 je time zatvorena (7a + 7b + 7c).

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

**Status: GOTOVO, uključujući rollback test.**

- **Backup** (tačka 1): artisan komanda `homeos:backup` u repou, pokreće je
  centralni scheduler svaki dan u 03:15. Radi dump baze (`mysqldump` prema
  hostovom MariaDB-u preko `host.docker.internal`) **i** ZIP arhivu korisničkih
  priloga — bez priloga bi vraćena baza pokazivala na nepostojeće fajlove.
  Rotacija briše starije od `BACKUP_KEEP_DAYS` (14). Deploy dodatno radi backup
  **prije migracija** (`CLAUDE.md` §17).
  - **Zamka s volumenom (naučeno u produkciji):** Docker imenovani volumen
    montiran na putanju koje **nema u image-u** kreira se kao `root:root`, a
    kontejner radi kao `www` — backup je tiho padao na `Permission denied`, dok
    je deploy prolazio jer korak nije fatalan. Rješenje je dvostruko: image od
    sada nosi prazan `storage/backups` (novi volumen naslijedi `www:www`), a
    deploy dodatno radi idempotentni `chown` za **već postojeći** volumen, jer
    njega Docker ne re-seed-a.
  - Backupi idu u imenovani volumen `app-backups`, **ne** u bind mount na host:
    dump baze bi tada morao biti čitljiv kontejnerskom korisniku, a na serveru s
    desetinama tuđih vhostova to znači i čitljiv drugima. Volumen se seed-a kao
    `www:www`, pa je zapisiv bez labavih dozvola.
  - Preuzimanje/kopija van servera:
    `docker compose -f docker-compose.prod.yml cp scheduler:/var/www/html/storage/backups ./backups`
  - Vraćanje: `mysql -u homeos -p homeosdb < baza_….sql`, pa raspakovati
    `prilozi_….zip` u `storage/app` (volumen `app-storage`).
- **Monitoring** (tačka 2): javni `/zdravlje` provjerava bazu, cache i storage i
  vraća **503** ako je bilo šta palo — deploy ga sada koristi umjesto `/login`,
  pa "stranica se renderuje, ali baza je pala" više ne prolazi kao uspješan
  deploy. Neuspio noćni backup šalje email na `HOMEOS_ALERT_EMAIL` (fallback:
  vlasnik prvog domaćinstva), kroz Notification sistem.
- **Resource limits** (tačka 4): scheduler podignut na 512M — noću radi
  `mysqldump` + ZIP arhivu, pa mu je 256M tijesno. Ostali servisi ostaju kako
  jesu; opterećenje je i dalje jednog domaćinstva.
- **Izolacija** (tačka 5): zahtijeva shell na serveru, pa je ostavljena kao
  kontrolna lista za vlasnika (vidi `SUBMISSION.md`) — Docker stack i dalje
  izlaže samo `127.0.0.1:8091`, a novi volumen ne dira ništa izvan projekta.
- **Rollback provjera** (tačka 3): izvedena **na produkciji**, ne simulirana.
  Verzija je podignuta na `1.0.1` i deployana (`/zdravlje` to potvrdio), pa je
  na toj verziji **dodan novi zadatak i novi ljubimac**, pa je izmjena vraćena
  `git revert`-om i ponovo deployana. Rezultat: `/zdravlje` javlja `1.0.0`, a
  brojevi su ostali identični (`zadaci=12 podsjetnici=22 ljubimci=3 racuni=6
  clanovi=8`) — dakle revert vraća **kod**, a ne dira podatke, ni one nastale u
  međuvremenu.
  - Usput otkriveno i ispravljeno: verzija je stajala u `.env`, pa se morala
    ručno mijenjati na serveru i već je bila odstupila. Prebačena je u kod
    (`config/homeos.php`), gdje joj je i mjesto — inače bi footer iz Faze 9
    prikazivao pogrešan broj.

---

## Faza 9 — Polish i dokumentacija

**Status: u toku, u tri koraka.**
- **9a — GOTOVO:** rebrend u „HomeOS plus" (naziv i znak iz koda, ne iz `.env`),
  SVG monogram kao logo i favicon, footer s verzijom, `/zdravlje` → `/health`,
  zvonce kao Livewire komponenta koja se osvježava sama.
- **9a dorada:** footer prepravljen u „© @elvismemic <tekuća godina> · v<verzija>";
  naziv ispravljen u „HomeOS plus" (razmak je falio u blade-u, pa se prikazivalo
  „Home OSplus"); **email obavještenja su bila u kvaru** — notifikacije su gradile
  tenant-scoped URL bez tenanta, pa je u scheduler kontekstu email padao dok je
  in-app obavijest stizala. Zapisano u `RULES.md` §11.
- **9b — GOTOVO:** jezici bs/en/de. Puni prijevodi svih 11 fajlova po jeziku
  (~700 ključeva) + `lang/de.json` za Laravelove mail stringove; prekidač sa
  zastavicama (SVG, ne emoji — Windows ne renderuje emoji zastave) u traci i na
  prijavi; jezik se pamti u sesiji za gosta i u `users.locale` za prijavljenog;
  **email ide na jeziku primaoca** (`HasLocalePreference` na `User` i
  `HouseholdMember`, jer je notifiable član, a jezik korisnikov); pozivnica na
  jeziku onoga ko poziva (primalac još nema nalog). Test parnosti ključeva
  (bs/en/de) čini nedostajući prijevod greškom, ne tihim sirovim ključem — i
  odmah je otkrio da je `validation.password` u bosanskom bio zaostao kao ravan
  string, pa su poruke o jačini lozinke padale na engleski.
  Labele navigacionih grupa u `HomePanelProvider` su prebačene u closure:
  panel se gradi prije nego middleware postavi jezik, pa je direktan `__()`
  zamrzavao bosanske nazive i redoslijed menija se raspadao na drugom jeziku.
- **9c** — završni prolaz. Uz planirano (UX na tri širine, sigurnosni pregled,
  README/CLAUDE) ušli su i zahtjevi vlasnika:
  - **Dokumentacija u `docs/`** — u korijenu ostaju `README.md` i `CLAUDE.md` od
    nekoliko linija koji uvozi `docs/CLAUDE.md` (Claude Code čita korijenski
    `CLAUDE.md` sam; bez uvoza bi pravila prestala da se učitavaju).
    `PRAVILA.md` → `RULES.md`; sve reference usklađene.
  - **Gradient i stakleni efekat** — topla paleta (terakota → medena → krem):
    podloga stranice, traka i meni, kartice, prijava, hero. Zamagljenost iza
    panela je isti recept koji su već imali brzo dodavanje i pretraga, pa je
    efekat kroz aplikaciju jedan; sami paneli ostaju puni radi čitljivosti.
    Rezerve za `prefers-reduced-transparency` i za browser bez `backdrop-filter`.
  - **Zvonce otvara panel s desne strane** na širokim ekranima (korisnik pročita
    i potvrdi bez napuštanja stranice); na uskim i dalje vodi na punu stranicu.
    Logika sandučeta je izdvojena u trait, pa panel i stranica ne mogu odstupiti.
  - **Nazivi aplikacija i dugmad brzog dodavanja nisu pratili jezik** — dopuna
    Faze 9b: `config/homeos-apps.php` je nosio gotov tekst, a config se u
    produkciji kešira, pa je `__()` u njemu zamrzavao jezik. Sada nosi prijevodne
    ključeve koje razrješavaju `ModuleRegistry::name()` i `QuickCaptureRegistry`.
  - **„Sada" u brzom dodavanju podsjetnika** — ista radnja kao `suffixAction` na
    klasičnoj formi; kalendar i format (`d.m.Y H:i`, 24h) su već bili isti.
  - **Sinhronizacija jezika** localStorage ↔ nalog: prijavljenom je `users.locale`
    istina (klijent se poravnava po njemu), gostu se zapamćeni jezik vraća serveru
    jednom po otvaranju pretraživača.
  - **Sve rute na engleskom** (`RULES.md` §12): `/invitation/{token}`,
    `/language/{locale}`, `/search`, `/quick-add/{key}`, `/calendar/events`,
    `/profile/avatar/{user}`, slug `finance-overview`. Imena ruta nepromijenjena,
    pa nijedan `route()` poziv nije diran.
  - **Emailovi u stilu aplikacije** — vlastita markdown tema (`homeos.css`), znak
    i naziv u zaglavlju, potpis `©elvismemic v<verzija>` iz istog izvora kao
    aplikacija. Znak je složen HTML-om, ne SVG-om: Gmail izbacuje `<svg>`, a
    vanjske slike su blokirane dok korisnik ne dopusti prikaz.
  - **Sigurnosni pregled** — svaka javna ruta i endpoint koji upisuje podatke
    imaju `throttle`, i to provjerava test (`SecurityTest`), ne komentar.
  - **Ispravke nakon vlasnikove provjere:** `\"` u Alpine atributu je lomio brzo
    dodavanje na svakoj stranici (pokriveno `AlpineMarkupTest`-om); originalni
    logo u emailu (SVG rasterizovan u PNG, jer Gmail izbacuje `<svg>`); dugme u
    emailu na inline stil (`.inner-body a` je specifičnije od `.button`, pa je
    tekst dobijao boju linka); meni na mobilnom pun, bez stakla, jer tamo stoji
    preko sadržaja.

1. Testno pokrivanje ključnih tokova (feature testovi po modulu).
2. Ažuriranje `README.md` i `CLAUDE.md` sa svim naučenim tokom razvoja.
3. Finalni UX prolaz — sistematska provjera svih ekrana protiv `CLAUDE.md`
   tačke 6 (vizuelni dizajn i responzivnost); ovo je posljednja provjera,
   ne prva primjena pravila — svaki modul je od svoje faze već trebao
   ispuniti ta pravila kroz checklistu (tačka 14).
4. Sigurnosni pregled — rate limiting, CSRF, autorizacija po household-u
   (da član jednog domaćinstva ne može vidjeti podatke drugog).

**Dodano na zahtjev vlasnika (prije izrade Faze 9) — radi se PRVO, jer rebrend i
višejezičnost diraju svaki string u sistemu, pa finalni UX prolaz (tačka 3) ima
smisla tek nakon njih:**

5. **Rebrend „Home OS" → „HomeOS plus"** kroz cijeli sistem, a ne samo naslov:
   - `APP_NAME` (`.env.example`, `.env.prod.example`, CI/deploy okruženje),
     naslov panela i `<title>`, login/registracija, prazna stanja koja spominju
     naziv, email šabloni i potpis digesta, `README.md`/`SUBMISSION.md`.
   - Provjeriti da nigdje nije hardkodovan naziv u Blade/PHP-u — ide kroz
     `config('app.name')` odnosno prijevod (`RULES.md` §1).
   - Domena, ime repozitorija, Docker/DB imena i `homeos.imel.cloud` se **NE**
     mijenjaju (infrastruktura ostaje ista) — mijenja se samo ono što korisnik
     vidi. To eksplicitno navesti u commit poruci da se kasnije ne "ispravlja".
   - Ako se mijenja i logo/wordmark, ide u istu izmjenu (tema, tačka 6 CLAUDE.md).

6. **Višejezičnost: bosanski (default), engleski, njemački** kroz cijeli sistem:
   - Postojeći `lang/bs/*` je referentni set; dodaju se `lang/en/*` i `lang/de/*`
     s **istim ključevima** (nedostajući ključ mora biti greška u pregledu, ne
     tihi fallback na engleski Filamentov tekst). Isto važi za `lang/vendor/*`
     override-e i `lang/*.json`.
   - Izbor jezika **zastavicama, bez teksta** (`bs` / `en` / `de`), dostupan na
     **dvije tačke**: na **login formi** (prije prijave — bira se za sesiju) i u
     **topbaru** (nakon prijave). Zastavice moraju imati `aria-label`/`title` s
     nazivom jezika radi pristupačnosti (CLAUDE.md §6) — vizuelno bez teksta,
     ali ne i za čitače ekrana.
   - Odabir prijavljenog korisnika se **pamti na korisniku** (`users.locale`
     kolona već postoji u modelu — dodati migraciju ako nedostaje u bazi), a za
     neprijavljene u sesiji. Middleware postavlja `App::setLocale()` iz tog
     izvora, ne iz `Accept-Language`.
   - Email obavještenja i digest se šalju na jeziku **primaoca** (član, ne
     pošiljalac) — Notification klase moraju postaviti locale prije renderovanja.
   - Datumi/valuta ostaju po `RULES.md` §6 (24h, `d.m.Y`, KM) na svim
     jezicima — ne prevoditi format u lokalne konvencije.
   - Testovi: za svaki jezik jedan smoke test da se ključne stranice renderuju i
     da nema nedostajućih ključeva (usporedba skupa ključeva `bs` vs `en`/`de`).

7. **Footer aplikacije** — tanka traka na dnu svake stranice panela:
   „Powered by @elvismemic" + verzija. Footer ide kroz Filament render hook
   (`PanelsRenderHook::FOOTER`), diskretno, u skladu s temom i u light/dark
   varijanti.
   - **Verzija se NE piše u layout.** Postoji od Faze 8 u `config('homeos.version')`.
     Footer čita tu istu vrijednost koju prikazuje i health endpoint, pa su uvijek
     u skladu (usput je i dokaz koje je izdanje na produkciji).
   - **Nije u `.env`** (ispravljeno tokom rollback testa Faze 8): dok je bila env
     varijabla, morala se ručno mijenjati na serveru pri svakom izdanju i već je
     odstupila — kod je govorio 1.0.1, serverski `.env` 1.0.0, i `.env` je
     pobjeđivao. Verzija je svojstvo koda, pa se podiže u commitu.

8. **Zvonce se osvježava samo od sebe.** Brojač nepročitanih je server-renderovan
   u topbaru, pa nova obavijest stigne tek na sljedeće učitavanje stranice —
   npr. kad se podsjetnik okine s liste, obavijest je u sanducetu, a zvonce i
   dalje pokazuje staro stanje. (Postojeći `homeos-notifications-read` event
   pokriva samo suprotan smjer: kad se poruke označe pročitanim.)
   - Minimalno rješenje: zvonce postaje mala Livewire komponenta s `wire:poll`
     (npr. 30s) koja čita broj nepročitanih za trenutnog člana.
   - Ispravnije, ako vrijeme dozvoli: broadcast preko **Reverb**-a (već u stacku,
     `CLAUDE.md` §2) na privatni kanal člana kad stigne `database` obavještenje —
     bez pollinga i s trenutnim osvježavanjem.
   - Šta god se izabere, brojač mora ostati tačan i nakon „označi pročitanim“
     (ne smije se vratiti na staru vrijednost pri sljedećem pollu).

9. **Health ruta na engleski.** `/zdravlje` (Faza 8) preimenovati u `/health`.
   Korisnički vidljive rute su na također na engleskom (`/invitation/{token}`).
   Mijenja se zajedno: `routes/web.php`, URL u `.github/workflows/deploy.yml`
   (health-check korak), i spomeni u `SUBMISSION.md`/`ROADMAP.md`. Ime rute je
   već `health`, pa se `route('health')` ne mijenja.

---

## Napomena o redoslijedu

Faze 0, 0.5 i 1 su neopozive i idu tim redoslijedom — Faza 0.5 mora
potvrditi da deploy lanac radi prije nego što se gradi platform jezgro, a
svaka sljedeća faza pretpostavlja da event/notification/sharing mehanizam
iz Faze 1 postoji i ispravno radi. Ako se tokom Faze 3+ otkrije da nešto
nedostaje u platform jezgru, popravka ide nazad u Fazu 1, ne "zakrpa" u
modulu koji je otkrio problem.
