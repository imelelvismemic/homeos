<!--
Ovaj dokument je namijenjen ljudima koji ocjenjuju rješenje, ne Claude Code
agentu. Za operativni rad na projektu vrijede ROADMAP.md, CLAUDE.md,
DATA_MODEL.md i ORIGINAL_SPEC.md.

Pisan je USPUT, fazu po fazu, ne na kraju projekta — zato nosi i odluke koje
su se mijenjale i greške koje su usput otkrivene.
-->

# HomeOS plus — Sažetak rješenja

**Autor:** Elvis Memić
**Alat:** Claude Code (kompletna izrada, od arhitekture do deploymenta)
**Aplikacija:** https://homeos.imel.cloud

---

## Kako čitati ovaj dokument

Dokument je dugačak jer je pisan usput, kroz sve faze. Ako imate pet minuta,
dovoljne su prve tri stavke:

| Ako vas zanima… | Idite na |
|---|---|
| **Šta je isporučeno i u kojem roku** | [Rekapitulacija realizacije po danima](#rekapitulacija-realizacije-po-danima) |
| **Obim u brojevima** (testovi, moduli, kod) | [Brojevi na kraju](#brojevi-na-kraju) |
| **Šta je dodano iznad zadatka i zašto** | [Zaključak](#zaključak-šta-je-ušlo-a-zadatak-to-nije-tražio) |
| Kako je dokazana proširivost platforme | [Dokaz proširivosti](#dokaz-proširivosti-extensibility) |
| Zašto su tehničke odluke takve kakve su | [Arhitektonske odluke i obrazloženje](#arhitektonske-odluke-i-obrazloženje) |
| Kako je rješeno puštanje u rad | [Deployment](#deployment) |
| Detaljan tok rada, fazu po fazu | [Status projekta](#status-projekta) |

Prateći dokumenti, za onoga koga zanima kako se na sistemu radi dalje:
[`CLAUDE.md`](CLAUDE.md) (pravila razvoja i interfejsi),
[`ROADMAP.md`](ROADMAP.md) (faze i kriteriji završenosti),
[`DATA_MODEL.md`](DATA_MODEL.md) (šema podataka),
[`RULES.md`](RULES.md) (terminologija i UX pravila),
[`ORIGINAL_SPEC.md`](ORIGINAL_SPEC.md) (izvorni zadatak).

---

## Šta je zadatak tražio

Lični "kućni operativni sistem" — jedna aplikacija koja objedinjuje
svakodnevnu administraciju domaćinstva (zadaci, kalendar, podsjetnici,
finansije, bilješke...), dijeljena između članova domaćinstva, izgrađena
kao **platforma** na koju se mogu dodavati nove aplikacije bez izmjene
postojećeg koda. Zadatak je dobijen kao PDF na engleskom, bez definisane
tehnologije i bez smjernica za deployment.

Puni izvorni tekst: `ORIGINAL_SPEC.md` (prijevod).

---

## Rok i obim

Zadatak primljen srijeda 16h, rok predaje ponedjeljak 16h. Rad je uključio
i vikend (subota, nedjelja) — pet radnih dana ukupno umjesto tri, što je
omogućilo da se zadrži **pun obim** svih funkcionalnih modula iz brief-a,
bez unaprijed isključenih dijelova. Redoslijed rada (`ROADMAP.md`) je i
dalje postavljen po prioritetu — Faza 1 (platform jezgro), Faza 3
(Zadaci+Kanban+Kalendar) i Faza 7 (extensibility dokaz) su rađeni prvi, jer
najviše nose ciljeve izvedene ispod — ali kao redoslijed sigurnosti u
slučaju kašnjenja, ne kao trajno isključivanje modula.

---

## Ciljevi koje sam izvukao iz brief-a

Brief navodi osam funkcionalnih modula i detaljan set principa za
proširivost. Umjesto da ih tretiram kao ravnopravnu listu stavki za
"otkvačiti", izdvojio sam četiri cilja koja, po mom čitanju, nose najveću
težinu:

1. **Sistem mora dokazano djelovati povezano, ne samo deklarativno.**
   Brief eksplicitno kaže da je to "poenta" — jedan račun koji stvarno
   kreira zadatak, koji se stvarno pojavi u kalendaru, koji stvarno pokrene
   podsjetnik i email, jedan kroz drugi, bez ručnog povezivanja u svakom
   modulu.
2. **Proširivost mora biti dokazana, ne samo arhitektonski omogućena.**
   Brief detaljno razrađuje kako nova app treba da se uklopi bez diranja
   postojećeg koda — ovo tretiram kao zahtjev da se to i pokaže na
   konkretnom primjeru, ne samo opiše.
3. **Deployment je prvorazredni dio isporuke**, iako je zadan bez ikakvih
   smjernica — tretiram to kao namjernu provjeru samostalnosti, ne kao
   sporednu tehničku formalnost ostavljenu za kraj.
4. **Niska frikcija i domaćinstvo-na-prvom-mjestu** (iz "Guiding
   principles" dijela brief-a) kao kriterij za svaku UX odluku usput, ne
   samo kao završna napomena.

---

## Arhitektonske odluke i obrazloženje

Tehnologija nije bila zadana, pa su odluke donesene na osnovu: (a) postojećeg
znanja i infrastrukture koje već imam, radi realnog roka i pouzdanosti, i
(b) prirodne podudarnosti alata s onim što brief traži.

| Odluka | Obrazloženje |
|---|---|
| **Laravel** (backend) | Poznat stack; ima ugrađene mehanizme (Events, Notifications, Scheduler, Queues) koji direktno odgovaraju "sve je povezano" zahtjevu iz brief-a — nije trebalo graditi event-bus od nule. |
| **Filament v3** (UI) | Brz razvoj CRUD/dashboard/kanban ekrana; ugrađena multi-tenancy podrška mapirana direktno na koncept "domaćinstva" iz brief-a. |
| **MySQL + Redis** | Poznat stack, nema dodatnog troška. |
| **Resend** (email) | Transakcijski email bez upravljanja SMTP serverom; besplatan tier dovoljan za obim jednog domaćinstva. |
| **Docker + GitHub Actions** | Izolacija od desetina postojećih produkcijskih vhost-ova na istom serveru (server već hostuje mnoge domene preko Virtualmin-a); deploy opisan kao kod, ponovljiv i reverzibilan (`git revert` + redeploy). |
| **Postojeći Apache (Virtualmin)** kao reverse proxy | Server već drži SSL i rutiranje za sve postojeće domene — Docker stack izlaže samo interni port, Apache prosljeđuje saobraćaj; nema dupliranja SSL sloja. |
| **Postojeći Contabo VPS** (Debian/Ubuntu, Virtualmin) | Dodatni trošak = 0; deployment odluka nije prepuštena za kraj nego testirana rano (vidi niže). |

Potpuno obrazloženje i sve zaključane apstrakcije: `CLAUDE.md`.

---

## Proces rada s Claude Code

Prije nego što je napisana ijedna linija koda, definisana su četiri
dokumenta koja Claude Code prati kroz cijeli razvoj:

- **`ROADMAP.md`** — 11 faza od praznog scaffolding-a do produkcije, svaka
  sa jasnim "definition of done" kriterijem.
- **`CLAUDE.md`** — pravila razvoja i konkretni interfejsi (event-driven
  komunikacija između modula, sharing/permission mehanizam, dizajn sistem,
  checklist za svaki novi modul) — cilj je da svaki modul, uključujući onaj
  koji izgradi neko drugi ili sam Claude Code kasnije, bude jednako
  jednostavno uklopiv.
- **`DATA_MODEL.md`** — šema podataka zaključana unaprijed, da moduli ne
  izmišljaju paralelne konvencije za iste koncepte.
- **`ORIGINAL_SPEC.md`** — izvorni brief, referenca za namjeru kad
  operativni dokumenti ne daju jasan odgovor.

Namjera ovog pristupa: da se apstraktne/arhitekturne odluke donesu
promišljeno i jednom, prije izrade — a ne da ih Claude Code improvizuje
modul-po-modul usput, što bi vodilo nekonzistentnom sistemu.

Uz ta četiri temeljna dokumenta, tokom razvoja su nastali i "živi" prateći
dokumenti — kada se neko pravilo ili obrazac iskristališe, zapiše se da bi
ga svaki naredni modul (i sam Claude Code kasnije) slijedio, umjesto da se
iznova otkriva:

- **`.claude/skills/homeos-new-module/SKILL.md`** — reusable skill izdvojen u
  Fazi 3: kompletan obrazac za novi modul (Model, migracije, Policy, Filament
  Resource, DashboardWidget/SearchProvider/CalendarSource, notifikacije,
  scheduler, testovi, registracija) + naučene Filament zamke. Svaki modul od
  Faze 4 kreće od njega, ne od praznog fajla.
- **`RULES.md`** — pravila terminologije i pravopisa za sav
  korisnički tekst (veliko/malo slovo, dosljedni termini dugmadi
  "Sačuvaj"/"Zatvori", formati datuma, prazna stanja). Nastalo iz QA prolaza
  nakon Faze 3; povezano iz `CLAUDE.md` §13, poštuje ga svaki modul.
- **`app/Platform/README.md`** — vodič kroz ekstenzione tačke platforme
  (contracts, event/listener auto-discovery, registry) za autore novih modula.
- **`SUBMISSION.md`** — ovaj dokument; vodi se kao dnevnik napretka po fazama,
  ne piše se tek na kraju.

---

## Deployment

- Poddomena: `homeos.imel.cloud`
- Rani probni deploy (prazan skeleton, prije platform jezgra) namjerno
  urađen u Fazi 0.5 roadmap-a — da se problemi sa serverom/SSL-om/CI-jem
  otkriju prije nego je sistem izgrađen, ne poslije.
- **Live URL:** https://homeos.imel.cloud (prazan skeleton uživo — login,
  registracija, kreiranje domaćinstva, pozivanje člana, reset lozinke sa
  email obavještenjem preko Resend-a)
- **CI/CD:** GitHub Actions — CI (Pint + Pest) na svaki push/PR; deploy se
  automatski pokreće tek nakon zelenog CI-ja na `main` (SSH → git pull →
  `docker compose -f docker-compose.prod.yml up -d --build` → migrate →
  health-check na internom portu). Testirani rollback dolazi u Fazi 8.
- **Mrežna arhitektura:** Docker stack izložen samo na `127.0.0.1:8091`;
  postojeći Apache/Virtualmin drži SSL i reverse-proxya na taj port. Baza je
  postojeći hostov MariaDB (bez kontejnerizovane baze u produkciji), dostupan
  kontejnerima preko `host.docker.internal`. Potpuno izolovano od ostalih
  domena na serveru.

---

## Dokaz proširivosti (extensibility)

Dokaz je izveden u Fazi 7b modulom **Kućni ljubimci** (`app/Modules/Pets`) —
dodan je NAKON što je platforma bila gotova, prateći checklist iz `CLAUDE.md`
§14. Namjerno nije "dummy" nego modul koji se stvarno koristi: ljubimac +
termini njege (vakcina, veterinarski pregled, terapija) s datumom.

**Šta je bilo potrebno dodati:** samo folder `app/Modules/Pets`, dvije migracije
(`pets_pets`, `pets_care_records`), prijevode `lang/bs/pets.php` i **jedan unos u
`config/homeos-apps.php`**.

**Šta je dobio besplatno, bez ijedne linije koda van modula:**

| Mogućnost | Kako | Šta je trebalo u core-u |
|---|---|---|
| Stavka u navigaciji | Filament auto-discovery po folderu | ništa |
| Sažetak na početnoj | `DashboardWidgetContract` | ništa |
| Univerzalna pretraga | `SearchProviderContract` | ništa |
| Termini u kalendaru | `CalendarSourceContract` | ništa |
| Sedmični/dnevni sažetak | `DigestSourceContract` | ništa |
| „Brzo dodaj“ | `QuickCreateContract` | ništa |
| Privatnost i dijeljenje | `Shareable` + Policy | ništa |
| Prekidač u postavkama domaćinstva | `ModuleRegistry` čita registry | ništa |
| **Podsjetnik + email pred termin** | modul emituje platformski `ReminderRequested`; Podsjetnici ga uhvate | ništa |

Zadnji red je suština brief-a („sve je povezano"): modul Ljubimci **ne importuje
nijednu klasu iz Podsjetnika**, a korisnik ipak dobije podsjetnik i email tri
dana prije vakcinacije. Isti mehanizam kojim račun i dokument otvaraju podsjetnik.

Provjereno testovima (`tests/Feature/Pets`), uključujući i suprotan smjer —
modul radi i kad su **svi ostali moduli isključeni**, i uredno nestaje sa svih
ekrana kad domaćinstvo isključi njega, bez gubitka podataka.

Jedina svjesna odluka radi čistoće dokaza: modul koristi **postojeću** navigacionu
grupu „Administracija“. Nova grupa bi tražila dopunu `->navigationGroups([...])`
u core provideru — što je uredno dokumentovano u checklisti, ali bi značilo
izmjenu jednog core fajla.

---

## Status projekta

**Faza 0 završena** — Laravel 13 + Filament v3 skeleton, Docker Compose lokalni
dev, `Household`/`HouseholdMember` modeli, Filament auth + tenant registracija
+ invite-member UI, GitHub Actions CI, Pest testovi. Kroz lokalno testiranje
dorađeno: custom-tema/Tailwind, dev performanse (vendor van bind mounta,
opcache), bosanski prijevodi (forme, tabela, email), automatsko generisanje
APP_KEY-a.

**Faza 0.5 završena** — prazan skeleton uspješno deployan na
`https://homeos.imel.cloud` i potvrđena cijela produkcijska putanja:
- Docker produkcijski stack (`docker-compose.prod.yml`) bez kontejnerizovane
  baze — koristi hostov MariaDB preko `host.docker.internal`; Nginx samo na
  `127.0.0.1:8091`, Apache/Virtualmin SSL + reverse proxy.
- Automatski deploy (`deploy.yml`) radi kraj-do-kraja: push na `main` → CI
  zeleno → deploy zeleno (git pull → build → migrate → health-check), bez
  uticaja na ostale domene.
- Uživo provjereno: registracija, kreiranje domaćinstva, pozivanje člana,
  reset lozinke + dostava emaila preko Resend-a.

**Faza 1 završena** — platform jezgro na kojem grade svi budući moduli, pet
ekstenzionih tačaka koje modul koristi bez izmjene postojećeg koda (`CLAUDE.md`
§7–§11, vodič u `app/Platform/README.md`):
- **Eventi** — listener auto-discovery (`bootstrap/app.php`) kroz
  `app/Platform/Listeners` i `app/Modules/*/Listeners`; generički `Shared` event.
- **Notifikacije** — kanali `mail` + `database`, preferencije po članu i
  kategoriji; osnovna klasa `HouseholdNotification` bira kanale (in-app uvijek,
  email osim ako je član isključio). Notifiable je `HouseholdMember`.
- **Dijeljenje/privatnost** — generički `Shareable` trait + `shares`/
  `share_recipients` tabele (privatno / cijelo domaćinstvo / određeni članovi),
  sa izolacijom između domaćinstava; autorizacija ide kroz Policy → `isVisibleTo`.
- **Scheduler** — modul registruje periodični zadatak preko
  `routes/schedule.php`, centralni `ModuleSchedule` ga pokupi.
- **Pretraga** — `SearchProviderContract` + `SearchService` agregira providere
  iz `config/homeos-apps.php` (uz `DashboardWidgetContract` za Fazu 2).
  Univerzalna pretraga (command palette, Ctrl+K) dodana je naknadno, nakon
  Faze 3 — u Fazi 1 je postojao samo backend bez UI-ja. Iza reverse proxyja
  se pojavio 419 na `/livewire/update` (custom Livewire komponenta u render hooku
  ne dobija Filament panel kontekst na update-u → `getUrl()` TypeError → tihi
  419); riješeno postavljanjem Filament konteksta u `boot()` komponente (vidi
  napomenu u `ROADMAP.md` uz Fazu 1).

Dokaz "sve je povezano": bilo koji `Shareable` objekat podijeljen s članom
automatski pokrene `Shared` event → platform listener → `shared_with_you`
in-app + email obavještenje (uz poštovanje preferenci) — bez ijedne linije koda
u modulu. Testirano: 18 testova / 63 assertiona; CI zeleno; deployano na
produkciju (aditivne migracije).

**Faza 2 završena** — Dashboard, custom vizuelni identitet i quick capture
(`CLAUDE.md` §6):
- **Dizajn token sistem** (zaključan, izbor vlasnika): paleta "Topli dom"
  (terakota primarna, topli neutralni tonovi, semantic boje), Fraunces (naslovi)
  + Inter (tekst). Riješena i Filament v3 / Tailwind v3 tema (odgođena iz Faze 0):
  zaseban Tailwind v3 toolchain kompajlovan u Docker build koraku.
- **"Today" dashboard** sa signature elementom — dnevni-brief hero (pozdrav
  ovisno o dobu dana, datum na bosanskom, jednolinijski sažetak) iznad widget
  mreže; renderuje se čisto i sa 0 modula.
- **Widget agregacija** — `DashboardWidgetRegistry` čita `dashboard_widget` iz
  `config/homeos-apps.php` (bez upita u tuđe tabele).
- **Quick capture** — proširiv launcher u topbaru (render hook + registry),
  dostupan sa svake stranice; graceful prazno stanje.
- Bosanski svuda, pristupačnost (vidljiv focus, `prefers-reduced-motion`).

Testirano: 24 testa / 78 assertiona (uklj. dashboard sa 0 modula); Pint čist;
deployano i vizuelno potvrđeno na produkciji. Usput riješen i cross-platform
build (Vite 8 rolldown native binarke, npm#4828) i serviranje kompajliranih
asseta u produkciji.

**Faza 3 završena** — Zadaci + Kanban + Kalendar, glavni dokaz principa "sve
je povezano" i prvi modul-građanin izgrađen na platformi iz Faze 1/2:

- **Modul Zadaci** — pun Filament Resource (CRUD): rokovi, prioriteti, status
  (za uraditi / u toku / završeno), odgovorna osoba, podzadaci (relation
  manager), oznake (dijeljeni platform tagovi preko `Taggable`), ponavljajući
  zadaci. Prati cijelu §14 checklistu: `Shareable` privatnost, `TaskPolicy`,
  household tenancy (ownership preko relacije na zapisu, bez coupling-a Platform→modul),
  bosanski stringovi, migracije s `tasks_` prefiksom.
- **Ponavljanje** — završetak ponavljajućeg zadatka kroz `TaskCompleted` event →
  `SpawnRecurringTask` listener kreira sljedeću instancu s pomjerenim rokom
  (odluka: "sljedeća na završetak", bez materijalizacije budućih). RRULE podskup
  (FREQ + INTERVAL) u `RecurrenceService`.
- **Kanban** — custom Filament stranica, kolone = statusi, filter po tabli;
  drag & drop (HTML5 + Alpine) mijenja `Task.status`, uz touch-friendly padajući
  izbornik po kartici (CLAUDE §6). Isti Task podaci, bez zasebnog entiteta.
- **Kalendar** — mjesečni/sedmični/lista prikaz (FullCalendar v6, self-hosted
  preko Vitea — community plugin ne podržava Laravel 13, vidi napomenu u
  `ROADMAP.md`). Događaje agregira platforma preko `CalendarSourceContract`; ne
  duplira task podatke i ne zna za Tasks.
- **Obavještenja** — `task_assigned` (event → listener) i `task_due_soon`
  (centralni scheduler → `tasks:notify-due-soon`), oba kroz `HouseholdNotification`.
- **Reusable skill** — obrazac modula izdvojen u
  `.claude/skills/homeos-new-module/SKILL.md` za sve naredne module.

Dokaz "sve je povezano" (Definition of done): zadatak s rokom se AUTOMATSKI
pojavi na dashboardu, kalendaru, kanban tabli i u pretrazi — bez ijedne linije
ručnog povezivanja u tim modulima (svi čitaju isti Task preko registryja /
`CalendarSourceContract`). Pokriveno integracijskim testom.

Testirano: **44 testa / 129 assertiona** (cijeli paket, uklj. 18 novih za
Fazu 3 — CRUD, svaki event, sharing/privacy kroz Resource, ponavljanje,
obavještenja, i integracija dashboard+kalendar+kanban+pretraga); Pint čist.

**Faza 4 završena** — Podsjetnici + Bilješke (drugi/treći modul-građanin,
izgrađeni po `homeos-new-module` skillu):

- **Podsjetnici** — samostalni ili (opciono) polimorfno vezani za bilo koji
  entitet (`remindable`); jednokratni i ponavljajući (dijeljeni
  `App\Platform\Recurrence\RecurrenceService`); centralni scheduler ih okida na
  `due_date` (`reminders:fire`, svake minute) → in-app/email obavještenje
  (`reminder_fired`) + spawn sljedeće instance. Automatski na kalendaru/
  dashboardu/pretrazi preko istih kontrakata kao Zadaci.
- **Bilješke** — rich text (Filament RichEditor), platform oznake (`Taggable`),
  `journal_date` za dnevničke unose (filter "Samo dnevnik"), polimorfna veza
  `notable`. Na dashboardu (nedavne) i u pretrazi.
- **Dokaz "sve je povezano" (DoD Faze 4):** podsjetnik/bilješka se kreiraju
  vezani za postojeći entitet KROZ javni interfejs — generički platform eventi
  `ReminderRequested`/`NoteRequested` koje modul-izvor emituje, a Podsjetnici/
  Bilješke uhvate i kreiraju zapis s polimorfnom vezom. Zadaci imaju akcije
  "Podsjeti me" / "Dodaj bilješku" koje emituju te evente — Zadaci NE importuju
  module Podsjetnici/Bilješke (nema cross-module zavisnosti, nema direktnog
  pristupa bazi). Pokriveno testom.

Testirano: **69 testova / 217 assertiona** (cijeli paket, uklj. 14 novih za
Fazu 4 — model/scheduler/ponavljanje, sharing, oba Resource-a, DoD event-
vezivanje i integracija kalendar+dashboard+pretraga); Pint čist.

**Faza 5a završena** — Finansije (isporuka Faze 5 u dva koraka; Life admin slijedi):

- **Transakcije** (prihod/rashod) po kategorijama, s platiocem i (opciono)
  podjelom troška među članovima (`BalanceService` računa "ko duguje kome").
- **Računi/pretplate** s dospijećem, ponavljanjem i `remind_days_before`;
  **budžeti** po kategoriji/mjesecu; **mjesečni pregled** (prihod/rashod, po
  kategoriji vs budžet, saldo članova).
- **Dokaz "sve je povezano" (DoD Faze 5):** na kreiranju računa Finance emituje
  `App\Platform\Events\ReminderRequested` (X dana prije dospijeća) → Podsjetnici
  kreiraju podsjetnik → scheduler ga okine → `reminder_fired` email. Nijedna
  linija koda van modula Finansije; račun se pojavljuje i na kalendaru/
  dashboardu/pretrazi. Pokriveno testom.

Testirano: **84 testa / 261 assertion** (cijeli paket, uklj. 15 novih za Fazu 5a —
model/DoD/ponavljanje/saldo/oba Resource-a/integracija); Pint čist.

**QA prolaz Finansije** (nakon korisničke provjere):

- **Univerzalna pretraga** sada uključuje i kategorije i budžete (uz račune i
  transakcije); pretraga računa/transakcija hvata i naziv kategorije.
- **Format iznosa** ujednačen kroz cijeli modul preko `Finance\Support\Money::km()`
  → `700.00 KM` (umjesto Filament defaulta `BAM 700.00`) — liste, kalendar,
  mjesečni pregled, widget, podsjetnik.
- **Naslovi formi** (dodaj/uredi) za kategorije/budžete/račune/transakcije usklađeni
  s `RULES.md` (malo slovo u drugoj riječi); **brisanje budžeta** prikazuje
  naziv zapisa u modalu kao ostali moduli.
- **Odabir mjeseca budžeta** je sada `Select` (mjesec+godina), bez biranja dana.
- **Račun**: inline "brzo dodaj kategoriju" na formi + filter po kategoriji na listi.

**QA prolaz Finansije (2)** (dodatna korisnička provjera):

- **Brzo dodaj kategoriju** i na formi budžeta (kao na računu).
- **Pretraga liste računa** sada hvata i naziv kategorije (kolona `category.name`
  označena `searchable`).
- **Plaćanje računa → trošak** (novo, "sve je povezano"): kad se račun označi
  plaćenim, Finance automatski bilježi `expense` transakciju vezanu za taj račun
  (`bill_id`) pa se pojavljuje u mjesečnom pregledu; veza daje idempotenciju (jedan
  trošak po plaćanju) i nasljeđuje privatnost računa. Event-driven (`BillPaid` →
  `RecordBillPayment`), bez koda van modula Finansije. Pokriveno testovima
  (kreiranje/idempotencija/privatnost).

**Faza 5b završena** — Life admin (Administracija domaćinstva):

- **Evidencija** — jedinstven model **Dokument** s tipom (lična isprava, garancija,
  obnova/registracija, ugovor, ostalo), datumom isteka i **prilogom** (skenovi/PDF).
  Zaseban model **Kontakti** (majstori, ljekari, komšije — bez datuma).
- **Prilozi na privatnom disku** — skenovi se čuvaju na disku `documents`
  (`storage/app/documents`), NIKAD u `public/`; preuzimanje ide kroz autentikovanu
  Filament akciju (Policy `view`), pa privatni dokument ne curi direktnim URL-om.
  Dodan perzistentni Docker volumen `app-storage` (dijeljen app/queue/scheduler) da
  upload-i prežive redeploy.
- **Zajedničke liste za kupovinu** — lista + stavke koje se štikliraju (`is_done`,
  ToggleColumn — niska frikcija). Kućanski poslovi idu kroz modul Zadaci (odluka
  vlasnika), bez dupliranja.
- **Dokaz "sve je povezano" (DoD Faze 5b):** na kreiranju dokumenta s datumom isteka
  Life admin emituje `App\Platform\Events\ReminderRequested` (X dana ranije, default
  30) → Podsjetnici kreiraju podsjetnik → scheduler → `reminder_fired` email. Nijedna
  linija van modula; dokument se pojavljuje i na kalendaru/dashboardu/pretrazi.
- Registrovan u `config/homeos-apps.php` (dashboard widget, search provider, calendar
  source); prijevodi u `lang/bs/lifeadmin.php`; tri Policy klase; DATA_MODEL.md §4c.
  Pokriveno testovima (model/DoD/privatnost/upload/oba Resource-a/integracija).

**Faza 6a — Dijeljenje + upravljanje članovima** (dio 1 od 2; 6b: obavještenja + digest):

- **Zajednički "Podijeli" mehanizam** (`App\Platform\Filament\Sharing\SharingForm`)
  — modul-neutralna akcija (na listi i na edit stranici) za svaki Shareable entitet:
  Privatno / Cijelo domaćinstvo / Određeni članovi. Vidljivost se čuva u `shares`
  tabeli (Shareable trait), ne na modelu. Zakačeno na svih 8 entiteta (zadaci,
  bilješke, podsjetnici, računi, transakcije, dokumenti, kontakti, liste).
- **Upravljanje članovima** (`HouseholdMemberService` + akcije na Resource-u):
  promjena uloge, uklanjanje člana, prijenos vlasništva — sve vlasnik-only, uz
  invariantu "domaćinstvo uvijek ima bar jednog vlasnika".
- Testovi: Share akcija (privatno/određeni članovi), member admin (uloga/uklanjanje/
  zadnji vlasnik/prijenos). Pint čist.

**Faza 6b — Obavještenja po članu + digest** (dio 2 od 2; zaokružuje Fazu 6):

- **Postavke obavještenja** (`NotificationSettings` stranica, po članu): email
  uključi/isključi po kategoriji + ritam digesta (bez / dnevni / sedmični).
  Kategorije se čitaju iz registryja (`NotificationCategoryRegistry` → svaki modul
  deklariše svoje u `config/homeos-apps.php`, platforma nosi `shared_with_you`).
  In-app obavještenja uvijek stižu; email samo ako kategorija nije isključena
  (`HouseholdNotification`). **DoD Faze 6 ispunjen** — član može isključiti sve
  emailove osim npr. `bill_due` i to se poštuje sistemski.
- **Digest email** (`DigestService` + `DigestNotification`, mail-only): scheduler
  šalje dnevni/sedmični sažetak članovima koji su odabrali ritam. Sadržaj agregira
  sve module kroz `DigestSourceContract` (registry `digest_source`) — nadolazeći
  zadaci, računi, podsjetnici i istek dokumenata koje član smije vidjeti. Prazan
  digest se ne šalje. Novo polje `household_members.digest_frequency` (opt-in).
- **In-app sanduče** (`NotificationsInbox`) + zvonce u topbaru s brojačem
  nepročitanih. Obavještenja idu na `HouseholdMember` (per-domaćinstvo + email
  preferencije po članu), pa native Filament zvonce (koje čita `User`) ne bi radilo —
  sanduče je scope-ovano na trenutnog člana, uz "označi pročitanim".
- **Privatnost izvedenih zapisa (ispravka):** izvedeni podsjetnik/bilješka/trošak
  sada nasljeđuje vidljivost izvora (`App\Platform\Sharing\VisibilityMirror` + event
  `VisibilityChanged` koji Shareable emituje pri promjeni vidljivosti; Reminders/Notes
  slušaju i usklade svoje izvedene zapise). Privatan račun povlači privatan podsjetnik
  — naziv privatne stavke više ne curi domaćinstvu kroz izvedeni zapis.
- Testovi: registry kategorija, agregacija digesta uz poštovanje privatnosti, slanje
  po ritmu/prazan digest/mail-only kanal, snimanje postavki, in-app sanduče
  (nepročitano/označi pročitanim), propagacija privatnosti računa na podsjetnik.

**QA prolaz kroz cijeli sistem (prije Faze 7)** — vlasnički pregled aplikacije;
ispravke koje su dotakle više modula odjednom:

- **Vremenska zona** — `config/app.php` je imao hardkodovan `'UTC'` i ignorisao
  `APP_TIMEZONE`, pa je pozdrav u 06:55 glasio „Dobro veče“ (04:55 UTC), a
  podsjetnici su okidali dva sata iza upisanog vremena. Sada čita
  `env('APP_TIMEZONE')` → `Europe/Sarajevo`.
- **Podsjetnici — okidanje na jednom mjestu** (`ReminderFirer`): scheduler, lista,
  forma podsjetnika i dashboard widget rade istu stvar, a `completed_at` se upisuje
  PRIJE slanja obavještenja. Time je riješen bug u kojem je pad slanja emaila
  ostavljao podsjetnik neokinutim, pa ga je scheduler ponavljao — i punio sanduče
  istim obavještenjem — svake minute. Ručno okidanje sada šalje obavještenje kao i
  automatsko (`DATA_MODEL.md` §4a).
- **Kalendar** — klik na dan otvara „Brzo dodaj“ s postavljenim datumom (spec:
  „dodajte zadatak, bilješku ili podsjetnik odakle god“), 24-satni prikaz vremena.
- **Bilješke/dnevnik** — kartica „Dnevnik“ na listi, akcija „Dnevnik za danas“ i
  unosi dnevnika na kalendaru (`JournalCalendarSource`), pa datum dnevnika ima
  stvarnu svrhu; iz editora uklonjeno nefunkcionalno prilaganje fajlova.
- **Profil korisnika** s promjenom lozinke koja traži potvrdu trenutne lozinke.
- **Upload dokumenata > 2 MB** — bazni PHP image ne aktivira `php.ini`, pa su
  vrijedile ugrađene vrijednosti (`upload_max_filesize=2M`) i veći prilog je tiho
  padao, ostavljajući dugme „Sačuvaj“ zaglavljenim. Dodan `docker/php.ini`
  (20M/24M) s limitima poredanim tako da aplikacija uvijek javi grešku prije nego
  je presretne sloj ispod.
- **Sanduče obavještenja** — pročitana skrivena po defaultu, brojač na zvoncetu se
  osvježava odmah; **„Pozovi“** vidi samo vlasnik domaćinstva.
- **Jezik** — „Columns“ → „Kolone“ na svim listama (tačan paketski ključ) i
  ujednačen termin „lozinka“ umjesto „šifra“ kroz cijeli sistem. Naučeno pravilo
  zapisano u `RULES.md` (§1, §3, §7, §8) da se ne ponovi u novim modulima.

**Drugi krug QA-a** (nakon iste provjere vlasnika):

- **Naziv domaćinstva** mijenja vlasnik kroz „Postavke domaćinstva“; pristup ide
  kroz postojeću `HouseholdPolicy::update` — bez ijedne ručne `if` provjere.
- **Profil je vraćao 500** — Filamentov `->profile()` se registruje izvan tenant
  rute, pa panel layout puca kad navigacija zatraži URL vezan za domaćinstvo.
  Zamijenjen vlastitom stranicom u panelu, uz **profilnu sliku** (dodavanje i
  uklanjanje) na privatnom disku, serviranu autentikovanom rutom.
- **Kalendar** dohvata događaje kao feed po prikazanom rasponu, pa se nakon
  brzog dodavanja osvježi bez promjene mjeseca; izabrani dan se prosljeđuje
  unosu (rok zadatka, datum dnevnika, datum troška).
- **Brzo dodavanje** je prošireno: modul može registrovati više tipova unosa
  (`quick_capture` kao lista), pa Finansije nude i trošak i **račun**.
- **„Nazad“ s forme uređivanja** vodi na listu (`CancelReturnsToList`), a ne na
  formu dodavanja s koje je korisnik došao — pravilo u `RULES.md` §9 i u
  checklisti novog modula.
- **Mobilna navigacija** — meni više ne završava ispod URL trake browsera.

**Treći krug QA-a — konsolidacija postavki:** „moje postavke“ i „postavke
domaćinstva“ su svedene na dvije stranice umjesto četiri stavke menija.

- **Profil korisnika** je dobio kartice: Nalog (podaci + slika), Lozinka i
  **Obavještenja** (email po kategoriji + ritam sažetka, ranije zasebna stavka).
- **Postavke domaćinstva** sada nose i **članove**: listu vidi svaki član, a
  radnje (pozovi, uloga, prijenos vlasništva, uklanjanje) i izmjenu naziva samo
  vlasnik. `HouseholdMemberResource` je uklonjen — jedna stranica, jedno mjesto.
- **Profilna slika se nije prikazivala** nakon snimanja: privatni disk nema
  javni ni privremeni URL, pa je Filament generisao neupotrebljiv link; pregled
  sada ide kroz istu autentikovanu rutu kao i avatar u panelu.
- Prevedeni „Save changes“ i cijeli uređivač slike, kroz `lang/vendor/*` — dakle
  spremno i za engleski/njemački iz Faze 9.

**Četvrti krug QA-a — navigacija i mobilni prikaz:**

- **500 na kreiranju domaćinstva:** stavka „Moj profil“ u korisničkom meniju
  gradi URL s `{tenant}` parametrom, a Filament ga računa prije provjere
  vidljivosti — na stranici gdje domaćinstva još nema to je rušilo cijelu
  stranicu. Sada se URL gradi samo uz tenant; test renderuje baš tu stranicu.
- **Kreiranje domaćinstva** više nije stalna opcija u meniju: forma je vidljiva
  samo korisniku koji još nije ni u jednom domaćinstvu, a Filament ga tamo i
  preusmjeri nakon prijave (pokriva „registrovao se pa zatvorio browser“).
- Meni je pročišćen: obavještenja se otvaraju zvoncetom, a grupa „Organizacija“
  ide redom Zadaci → Podsjetnici → Bilješke → Kanban → Kalendar.
- **Univerzalna pretraga** nalazi i članove trenutnog domaćinstva.
- **Mobilni**: meni je koristio `100vh` (visina bez donje URL trake browsera) pa
  se do zadnjih stavki nije moglo doskrolovati — sada `100dvh`; hamburger je
  prvi s lijeva, pa pretraga; meni se zatvara na klik bilo kojeg linka.

**Faza 7a završena** — App registry s uključenošću **po domaćinstvu** (dio 1 od 3;
7b: probna app kao dokaz proširivosti, 7c: pozivnica putem linka):

- `enabled` u `config/homeos-apps.php` je od sada samo **podrazumijevana**
  vrijednost. Stvarni odgovor daje `App\Platform\Modules\ModuleRegistry`, koji na
  config nakalemi izbor domaćinstva iz tabele `household_modules`. Tabela čuva
  samo **odstupanja** — domaćinstvo koje ništa nije mijenjalo nema nijedan red,
  pa novi modul odmah radi svima, bez popunjavanja tabele unazad.
- Prepravljeni su **svi** agregatori da pitaju registry: dashboard, univerzalna
  pretraga, kalendar, digest, brzo dodavanje i kategorije obavještenja. Ranije je
  svaki čitao `enabled` samostalno, pa bi gašenje modula radilo samo dijelom.
- **Navigacija i rute** su bile najveća rupa: modul bi nestao s dashboarda, ali
  bi stavka u meniju ostala i URL bi i dalje radio. Novi trait
  `App\Platform\Filament\Concerns\BelongsToModule` izvodi ključ modula iz
  namespace-a (`App\Modules\Tasks\…` → `tasks`) — istom konvencijom foldera kojom
  Filament već auto-discoveruje resurse, pa core i dalje ne drži listu klasa po
  modulu. Isključen modul nestaje iz menija i vraća 403 na svojoj ruti.
- Prekidači su na stranici **Postavke domaćinstva**, uz naziv i članove: vlasnik
  ih mijenja, član ih vidi onemogućene. Isključenje **ne briše podatke**.
- Kalendar je dodan u registry kao "potrošač" (nema svoj entitet ni providere,
  prikazuje ono što drugi moduli prijave) — inače se, po odluci "svi moduli se
  mogu isključiti", ne bi mogao ugasiti.
- Testovi: default vs. izbor domaćinstva, nestanak modula sa svih agregiranih
  ekrana uz očuvane podatke, skrivanje iz navigacije + 403 na ruti, **render
  dashboarda sa svim modulima isključenim**, prekidači kao vlasnik i kao član.
- Uz to dvije QA sitnice: redoslijed grupa u meniju (Organizacija, Finansije,
  Administracija) i redoslijed u topbaru na mobilnom (hamburger pa pretraga) —
  prvi pokušaj nije radio zbog specifičnosti CSS selektora.

**Faza 7b završena** — probna app **Kućni ljubimci** kao dokaz proširivosti
(dio 2 od 3; 7c: pozivnica putem linka). Detalji su u sekciji „Dokaz
proširivosti" iznad; ukratko:

- Modul `app/Modules/Pets`: **Ljubimac** (ime, vrsta, datum rođenja, bilješka) i
  **Njega** (vakcina / pregled / terapija / njega dlake, s terminom i brojem dana
  za podsjetnik). Njega se vodi uz ljubimca (RelationManager), bez zasebne stavke
  u meniju.
- Uklopljen jednim unosom u `config/homeos-apps.php` — dashboard, pretraga,
  kalendar, sažetak, „Brzo dodaj", dijeljenje i prekidač modula dolaze sami.
- **Termin njege otvara podsjetnik** kroz platformski `ReminderRequested`, bez
  importa modula Podsjetnici — isti mehanizam kao računi i dokumenti.
- 14 testova, uključujući rad s isključenim ostalim modulima i nestajanje sa
  svih ekrana kad se modul isključi (uz očuvane podatke).
- Uz to: „Pozovi člana" premješteno uz samu listu članova, i razdvojene dvije
  prazne poruke na početnoj („nema uključenih aplikacija" vs. „danas nema šta
  prikazati") — ranije je i drugi slučaj tvrdio da aplikacije nisu instalirane.

**Valuta i kapitalizacija** (uz Fazu 7c, prijavljeno tokom provjere 7b):

- **Valuta je postavka domaćinstva** (`households.currency`, izbor iz 29 svjetskih
  valuta, podrazumijevano EUR). Hardkodirani „KM" je uklonjen iz svih formi,
  tabela, widgeta, kalendara, sažetka i podsjetnika — ispis ide kroz
  `App\Platform\Support\Currency`. Polje se nudi samo ako je uključena aplikacija
  s iznosima, i to preko registry ključa `uses_currency` — platforma ne poznaje
  modul „finance" po imenu. Postojeća domaćinstva su migracijom prebačena na BAM
  (njihovi iznosi su unošeni kao marke; tihi prelazak na EUR bi promijenio
  značenje podataka). Pravilo: `RULES.md` §10.
- **Kapitalizacija naslova riješena na izvoru:** Filament title-case-uje naslove
  izvedene iz labela („Kućni Ljubimci"), što se dotad krpilo po stranici i vraćalo
  sa svakim novim modulom. Sada svi Resource-i modula nasljeđuju
  `App\Platform\Filament\Resources\ModuleResource`, koja gasi taj Filament
  prekidač (i nosi pripadnost modulu iz 7a). Pravilo: `RULES.md` §2.
- Brzo dodavanje je dobilo tip polja `select` (opcije se razrješavaju u zahtjevu,
  radi prijevoda), pa je vrsta ljubimca obavezna i tamo, kao i na punoj formi.

**Faza 7c završena — pozivnica putem linka** (dio 3 od 3; Faza 7 je time zatvorena):

- Vlasnik unosi email **bez obzira ima li osoba nalog**. Ako ima — odmah postaje
  član (kao i ranije). Ako nema — dobija email s jednokratnim linkom.
- Link vodi na registraciju s **popunjenim i zaključanim** emailom; po otvaranju
  naloga osoba ulazi **pravo u domaćinstvo** u koje je pozvana, bez koraka
  „kreirajte svoje domaćinstvo“. Time je zatvorena rupa u kojoj je pozvani član
  morao prvo napraviti prazno domaćinstvo koje mu ne treba.
- Prihvatanje radi listener na **Login** eventu, pa isti mehanizam pokriva i
  onoga ko se registruje kroz link i onoga ko već ima nalog pa se prijavi.
- Sigurnost: u bazi stoji samo **hash** tokena (sam token postoji jedino u linku),
  pozivnica je jednokratna i ističe za 7 dana, a prihvatanje provjerava da se
  email naloga poklapa s emailom pozivnice — proslijeđen link ne uvodi tuđi nalog
  u domaćinstvo. Email ide kroz Notification sistem, nikad direktan `Mail::send`.
- Vlasnik na stranici Postavke domaćinstva vidi poslane pozivnice i može ih
  povući; član ih ne vidi.
- 8 testova pokriva sva četiri toka i sve tri sigurnosne provjere.

**Faza 8 završena — produkcijsko dovršavanje deploy lanca:**

- **Dnevni backup** je artisan komanda u repou (`homeos:backup`), a ne cron
  skripta na serveru — tako je strategija vidljiva u kodu, pokrivena testovima i
  preživi seobu servera. Pokreće je postojeći scheduler u 03:15: dump baze
  (`mysqldump` prema hostovom MariaDB-u) **i** arhiva korisničkih priloga, uz
  rotaciju starijih od 14 dana. Deploy dodatno radi backup **prije migracija**.
- Backupi idu u imenovani Docker volumen, **namjerno ne u folder na hostu**: dump
  baze bi tada morao biti čitljiv kontejnerskom korisniku, a server hostuje
  desetine tuđih domena — to bi značilo i čitljiv drugima. Komanda za preuzimanje
  je u `ROADMAP.md` (Faza 8).
- **Health endpoint `/zdravlje`** provjerava bazu, cache i storage i vraća 503 ako
  je bilo šta palo. Deploy ga sada koristi umjesto `/login`, pa „stranica se
  renderuje, ali baza je pala" više ne prolazi kao uspješan deploy. Spreman je i
  za vanjski uptime monitor.
- **Neuspio backup ne prolazi tiho** — ide email na adresu za tehnička upozorenja
  (`HOMEOS_ALERT_EMAIL`, fallback vlasnik prvog domaćinstva), kroz Notification
  sistem, a ne kao `Mail::send`.
- 10 testova: sadržaj arhive, rotacija (i da ne dira tuđe fajlove u folderu),
  email na neuspjeh, fallback adresa, sigurnost `mysqldump` poziva (lozinka nikad
  u argumentima, jer su vidljivi u `ps`), i tri za health endpoint.

**Rollback je testiran na produkciji, ne simuliran.** Verzija je podignuta na
`1.0.1` i deployana, na njoj su **dodani novi zadatak i novi ljubimac**, pa je
izmjena vraćena `git revert`-om i ponovo deployana. Nakon reverta `/zdravlje`
javlja `1.0.0`, a brojevi zapisa su nepromijenjeni (`12 / 22 / 3 / 6 / 8`) —
uključujući ono što je nastalo dok je live bila verzija koja se povlači. Dakle
revert vraća kod, a ne dira podatke.

Test je usput otkrio stvarnu manu: verzija je stajala u `.env`, pa se morala
ručno mijenjati na serveru pri svakom izdanju — i već je bila odstupila (kod
1.0.1, serverski `.env` 1.0.0, `.env` pobjeđuje). Prebačena je u kod
(`config/homeos.php`); inače bi footer iz Faze 9 mirno prikazivao pogrešan broj.

**Kontrolna lista za vlasnika** (traži shell na serveru, ne može iz koda):

```bash
# 1. Docker i dalje sluša samo na loopbacku
sudo ss -tlnp | grep 8091          # očekivano: 127.0.0.1:8091

# 2. MySQL korisnik vidi samo svoju bazu
mysql -e "SHOW GRANTS FOR 'homeos'@'localhost';"   # očekivano: GRANT ... ON `homeosdb`.*

# 3. Backup stvarno nastaje
docker compose -f docker-compose.prod.yml exec scheduler ls -lh storage/backups
```

**Faza 9a — rebrend i završni detalji** (dio 1 od 3):

- **„Home OS plus"** s vlastitim znakom: inline SVG monogram (krov s plusom u
  terakoti teme) u bočnom meniju, na prijavi i kao favicon — bez vanjskih asseta.
- Naziv i verzija **žive u kodu** (`config/homeos.php`), ne u `.env`. Rollback
  test Faze 8 je pokazao zašto: env varijabla se mora ručno mijenjati na serveru i
  već je bila odstupila. Footer i health endpoint čitaju istu vrijednost, pa ne
  mogu prikazati različite brojeve.
- **Footer**: „Pokreće @elvismemic · v1.0.0", diskretan, u light i dark varijanti.
- **`/zdravlje` → `/health`**: korisničke rute ostaju bosanske, ali tehnički
  endpoint koji zovu deploy skripta i monitori piše se engleski.
- **Zvonce se osvježava samo od sebe**: Livewire komponenta s `wire:poll` na 30s,
  plus trenutna reakcija na „označi pročitanim". Ranije je nova obavijest stizala
  tek na sljedeće učitavanje stranice — npr. kad se podsjetnik okine s liste.
  (Reverb bi bio trenutan, ali traži novi kontejner i izmjenu Apache vhosta;
  odluka vlasnika je bila polling.)
**Faza 9b — tri jezika** (dio 2 od 3):

- **bs / en / de**, prevedeno je sve što korisnik vidi: svih 11 prijevodnih
  fajlova po jeziku (~700 ključeva), prazna stanja, potvrde brisanja,
  validacijske poruke, Laravelovi mail stringovi (`lang/de.json`). Filament
  isporučuje svoje prijevode za sva tri jezika, pa paketski tekst prati izbor.
- **Prekidač sa zastavicama** stoji u traci i na stranici prijave. Zastave su
  inline SVG, ne emoji: Windows ne renderuje emoji zastave (🇧🇦 se prikaže kao
  slova „BA"), pa bi prekidač na najčešćem korisničkom sistemu izgledao pokvareno.
  Radi i bez JavaScripta — svaka opcija je obična forma.
- **Izbor se pamti gdje pripada**: gost u sesiji, prijavljeni korisnik u
  `users.locale` (kolona je postojala od Faze 7, pa nova migracija nije trebala).
  Registracija zadržava jezik odabran na formi.
- **Email ide na jeziku primaoca**, ne na jeziku procesa koji ga šalje. Laravel to
  poštuje kroz `HasLocalePreference` — implementiran i na `User` i na
  `HouseholdMember`, jer je notifiable član domaćinstva a jezik je korisnikov.
  Pozivnica ide na jeziku onoga ko poziva (primalac još nema nalog).
- **Nedostajući prijevod je greška, ne sirovi ključ u UI-ju**: test parnosti
  poredi ključeve bs/en/de za svaki fajl. Odmah je i pronašao stvarnu grešku —
  `validation.password` je u bosanskom bio zaostao kao ravan string iz starijeg
  Laravela, pa su poruke o jačini lozinke tiho padale na engleski.
- Labele navigacionih grupa su prebačene u closure. Panel se gradi pri
  registraciji providera, **prije** nego middleware postavi jezik, pa je direktan
  `__()` zamrzavao bosanske nazive: na njemačkom se grupe više nisu poklapale s
  onim što moduli vraćaju i redoslijed menija se raspadao.
- Email obavještenja se testiraju kroz **stvarni mailer** (`array` transport), ne
  kroz `Notification::fake()` — fake ne poziva `toMail()`, pa bi test prošao i da
  jezik primaoca uopšte ne radi (`RULES.md` §11).

**Faza 9c — završni prolaz** (dio 3 od 3, zatvara Fazu 9):

- **Dokumentacija je u `docs/`.** U korijenu ostaju `README.md` i `CLAUDE.md` od
  nekoliko linija koji uvozi `docs/CLAUDE.md` — Claude Code automatski čita
  korijenski `CLAUDE.md`, pa bi puno premještanje značilo da se pravila razvoja
  više ne učitavaju sama, tj. tiho zaobilaženje koje niko ne bi primijetio.
  `PRAVILA.md` je preimenovan u `RULES.md`; sve reference u dokumentima i kodu su
  usklađene.
- **Gradient i stakleni efekat**, u paleti „Topli dom" (terakota → medena → krem):
  topla podloga stranice, staklena traka i meni, kartice, staklena kartica na
  prijavi, hero s gradijentom. Zamagljenost iza panela koristi **isti recept koji
  su već imali brzo dodavanje i univerzalna pretraga**, pa je efekat kroz
  aplikaciju jedan i prepoznatljiv; paneli su puni, jer tekst u njima ne smije
  zavisiti od toga šta je slučajno ispod. Hero gradient ide tako da je najsvjetliji
  ton u donjem desnom uglu, dalje od teksta, plus topla sjena preko zone naslova —
  bijeli tekst tako drži WCAG AA na svim širinama. Rezerve postoje za
  `prefers-reduced-transparency` i za browser bez `backdrop-filter`: površine
  postaju pune, jer staklo je ukras, ne nosilac informacije.
- **Zvonce otvara panel s desne strane** na širokim ekranima — korisnik pročita i
  potvrdi obavijest bez napuštanja onoga što radi. Na uskim ekranima panel te
  širine nije praktičan, pa zvonce vodi na punu stranicu; zato dva okidača
  razdvojena CSS-om, a ne grananje u JavaScriptu. Logika sandučeta je izdvojena u
  trait koji dijele panel i stranica — inače bi dva prikaza istog sandučeta
  vremenom počela pokazivati različit broj nepročitanih.
- **Dopuna Faze 9b koju je vlasnik uhvatio u testiranju:** nazivi aplikacija u
  postavkama domaćinstva i dugmad brzog dodavanja ostajali su na bosanskom.
  Uzrok: `config/homeos-apps.php` je nosio gotov tekst, a config se u produkciji
  kešira — `__()` u configu bi zamrznuo jezik onoga ko je pravio keš. Sada config
  nosi **prijevodne ključeve**, koje razrješavaju `ModuleRegistry::name()` i
  `QuickCaptureRegistry` pri prikazu. Test traži da se svaki ključ iz registryja
  stvarno razriješi u svim jezicima — pogrešan ključ se inače ne vidi kao greška
  nego kao sirovi tekst na ekranu.
- **„Sada" u brzom dodavanju podsjetnika** — ista radnja koju na klasičnoj formi
  nosi `suffixAction` na `DateTimePicker`-u. Kalendar i format (`d.m.Y H:i`, 24h)
  su već bili isti; jedina prava razlika ostaje sam widget (flatpickr vs Filamentov
  picker), jer bi Filamentov zahtijevao da modal postane Livewire komponenta — a
  on je namjerno bez Livewire-a (iza proxyja je `/livewire/update` obarao 419).
- **Jezik je sinhronizovan između pretraživača i naloga.** Prijavljenom korisniku
  je `users.locale` istina i klijent se poravnava po njemu — obrnuto bi značilo da
  tuđi izbor na zajedničkom računaru prepiše jezik naloga. Gostu se zapamćeni jezik
  vraća serveru jednom po otvaranju pretraživača, pa izbor preživi istek sesije.
- **Sve rute su na engleskom** (`RULES.md` §12): `/invitation/{token}`,
  `/language/{locale}`, `/search`, `/quick-add/{key}`, `/calendar/events`,
  `/profile/avatar/{user}`, slug `finance-overview`. URL je dio koda, ne korisnički
  tekst — jedna adresa mora voditi na isto mjesto na svim jezicima. Imena ruta su
  nepromijenjena, pa nijedan `route()` poziv nije diran.
- **Emailovi prate stil aplikacije** — vlastita markdown tema (`homeos.css`) s
  paletom i serifnim naslovom, znak i naziv u zaglavlju, potpis
  `©elvismemic v<verzija>` iz istog izvora kao u aplikaciji. Znak je složen HTML
  tabelom i bojama, **ne** SVG-om: Gmail izbacuje `<svg>` iz emaila, Outlook ga ne
  renderuje, a vanjske slike većina klijenata blokira dok korisnik ne dopusti
  prikaz — pa bi na mjestu logotipa stajao prazan okvir.
- **Sigurnosni pregled kao testovi, ne kao spisak namjera:** svaka javna ruta i
  endpoint koji upisuje podatke imaju `throttle` (prijava/registracija/obnova
  lozinke to već imaju od Filamenta), član jednog domaćinstva ne može ni ručno
  izmijenjenim linkom doći do tuđeg (`?h=`, tuđi tenant → 404, ne 403, da se ne
  potvrdi ni postojanje). Granica koja živi samo u komentaru pada prvom izmjenom
  koja je ne primijeti.
- **`README.md` prepisan** — naziv usklađen s rebrendom („HomeOS plus", ne
  „Home OS"), tabela cijele dokumentacije s putanjama u `docs/`, napomena o tri
  jezika i emailovima na jeziku primaoca, `/health` kao način da se provjeri
  stanje instalacije, i objašnjenje zašto korijenski `CLAUDE.md` ostaje.
- **`CLAUDE.md` (pravila razvoja) dopunjen onim što je faza naučila:** §13
  Lokalizacija sada nosi tri jezika, `Locales` kao jedini izvor istine, pravilo da
  novi ključ ide u sva tri jezika, pravilo da `__()` u kodu koji se izvršava pri
  registraciji providera zamrzne prijevod, i pravilo da email ide na jeziku
  primaoca. Struktura `lang/` u §4 pokazuje `en/` i `de/`; checklista za novi modul
  (§14) traži prijevode u sva tri jezika; §18 zabranjuje ključ dodan u samo jedan
  jezik. Preostala spominjanja starog naziva su ispravljena.
- **`RULES.md` §12 — nova sekcija „Rute su na engleskom"**: URL je dio koda, ne
  korisnički tekst; slug postavljen ručno je isto engleski; ime rute se ne mijenja
  kad se putanja mijenja (zato nijedan `route()` poziv nije diran); putanja koja je
  već otišla korisnicima ne smije prestati raditi; svaka javna ruta ima `throttle`.
- **Redirect sa stare bosanske putanje pozivnice je namjerno izostavljen** — prvo
  je bio dodan, jer pozivnice vrijede 7 dana i mogu čekati u sandučetima; vlasnik je
  potvrdio da nijedna nije poslana van razvoja, pa je uklonjen. Pravilo je ipak
  zapisano u `RULES.md`, uz napomenu zašto ovdje nije primijenjeno.
- **Prijevodi dopunjeni u sva tri jezika** za novi tekst ove faze: brzo dodavanje
  troška i računa (`finance.transactions.quick_capture`, `finance.bills.quick_capture`),
  panel obavještenja (`platform.inbox.close`, `platform.inbox.open_all`) i dugme
  „Sada" (`platform.quick_capture.now`). Test parnosti iz Faze 9b čuva da nijedan
  ne ostane samo u jednom jeziku.
- **Emailovi: šabloni objavljeni i tema uvezana** — `resources/views/vendor/mail`
  (HTML i plain-text varijanta), tema `homeos` registrovana u `config/mail.php`.
  Plain-text verzija nosi isti potpis, jer je to ono što vide klijenti koji ne
  prikazuju HTML.
- **Novi testovi u ovoj fazi:** `SecurityTest` (granice na svim javnim rutama i na
  endpointu koji upisuje podatke, izolacija domaćinstava kroz `?h=` i kroz tuđi
  tenant, obavezna prijava za stranice panela), `MailStyleTest` (znak, naziv,
  potpis, paleta i plain-text alternativa — kroz **stvarni** mailer, jer
  `Notification::fake()` nikad ne pozove `toMail()`), dva testa za panel zvonca
  (zatvoreno zvonce ne vuče listu; potvrda iz panela ne zatvara panel i javlja novi
  broj nepročitanih), i dva jezična (razrješavanje ključeva iz registryja u svim
  jezicima; nazivi aplikacija i dugmad brzog dodavanja na izabranom jeziku).
- **Build je provjeren, ne pretpostavljen** — `npm run build` prolazi s novom temom
  i sve nove klase (uključujući responzivne varijante `lg:flex` / `lg:hidden` za
  dvostruki okidač zvonca) su u kompajliranom CSS-u. Bez te provjere greška u temi
  bi pukla tek u deploy koraku.

**Šta u 9c ostaje na vlasniku:** vizuelna provjera na tri širine (375 / 768 /
1280 px) i u svijetloj i tamnoj temi. Agent nema pristup pretraživaču, pa je
gradient/staklo, panel zvonca i izgled emaila u stvarnom klijentu (Gmail, Outlook)
nešto što potvrđuje vlasnik — `CLAUDE.md` §6 to i traži kao dio „definition of
done", i zato se ovdje ne tvrdi da je već potvrđeno.

**Faza 9c — ispravke nakon vlasnikove provjere na produkciji:**

- **Brzo dodavanje je bilo puklo na svakoj stranici.** U komentaru unutar
  `x-data="{ ... }"` stajalo je `„Sada\"` — HTML atribut ne poznaje `\` escape, pa
  je taj navodnik **zatvorio atribut** i ostatak Alpine komponente se izlio na
  stranicu kao vidljiv tekst. Ispravljeno, i pokriveno testovima
  (`AlpineMarkupTest`): nijedan Blade ne smije sadržavati `\"`, komponenta brzog
  dodavanja mora biti jedan neprekinut atribut, i u tekstu dokumenta se ne smije
  pojaviti izvorni kod. Treći test je u prvoj verziji lažno padao jer je tagove
  skidao regexom, a Alpine atributi sadrže `=>` iz strelica-funkcija — zamijenjen
  je DOM parserom, koji zna razliku između atributa i teksta.
- **Originalni logo u emailu.** Prvo je znak bio složen HTML-om i bojama (zbog
  toga što Gmail izbacuje `<svg>`), ali je izgledao improvizovano. Sada je u
  emailu **isti znak koji nosi aplikacija**: `public/favicon.svg` rasterizovan u
  `public/email-logo.png` (144 px, prikazuje se na 36 px radi retina ekrana).
  Rasterizacija je nužna, ne izbor — SVG (pa i kao `data:` URI) kod većine
  primalaca ostaje nevidljiv. Uz sliku ostaje i naziv kao tekst, da zaglavlje
  ostane čitljivo ako klijent blokira slike.
- **Dugme u emailu je bilo neupotrebljivo** — debeli prsten i tekst koji se gubi.
  Dva uzroka: Laravelov CSS inliner primjenjuje `.inner-body a`, koje je
  specifičnije od `.button`, pa je tekst dobijao boju linka (terakota na
  terakoti); a `border`-trik default teme uz naš `padding` je davao dvostruki
  okvir. Dugme sada nosi **inline stil** (boja podloge na `<td>` + bijeli tekst na
  `<a>`) — u emailu je inline jedino što nijedan klijent ne nadjačava; mrtvi CSS
  je uklonjen.
- **Meni na mobilnom više nije proziran.** Na uskim ekranima meni stoji *preko*
  sadržaja, pa je staklo značilo da se tekst menija čita preko teksta stranice.
  Na tim širinama je podloga puna (svijetla/tamna), dok na desktopu — gdje meni
  ima svoju kolonu — staklo ostaje.

---

# Rekapitulacija realizacije po danima

Na početku dokumenta stoji plan; ovdje stoji šta je stvarno isporučeno i kada.
Podaci su izvedeni iz git historije (69 commita), ne iz naknadnog sjećanja.

**Okvir:** zadatak primljen u srijedu 22.07. u 16h, rok predaje ponedjeljak
27.07. u 16h — pet radnih dana uključujući vikend. Zadnji commit je u nedjelju
26.07. u 15:30: **puni obim je završen oko 24 sata prije roka, bez ijednog
izostavljenog modula.**

**Prije prvog commita** (srijeda 22.07. od 22h do 01h, pa četvrtak 23.07. od 9h
do 11h — ukupno oko 5 sati) radilo se na onome što se u git historiji ne vidi kao
kod, a odredilo je sve ostalo:

- **Razrada zadatka u operativni plan** i pisanje svih pratećih dokumenata koji su
  potom inicijalno pushani: `ROADMAP.md` (11 faza s kriterijima završenosti),
  `CLAUDE.md` (pravila razvoja, zaključani interfejsi i tehnički stack),
  `DATA_MODEL.md` (šema podataka i konvencije imenovanja polja) i
  `ORIGINAL_SPEC.md` kao referenca za namjeru zadatka. Sve globalne odluke — UI
  sloj, multi-tenancy, email provider, testing, autorizacija, lokalizacija —
  zaključane su **prije** Faze 0, upravo da se ne improvizuju usput po modulima.
- **Priprema okruženja i infrastrukture** (koraci koje radi čovjek, ne agent):
  Docker Desktop s WSL2 backendom, Docker CE na serveru, Virtualmin virtual server
  za `homeos.imel.cloud` sa SSL sertifikatom, provjera Apache proxy modula,
  izolovan MySQL korisnik i baza, provjera slobodnog loopback porta (8091), GitHub
  repozitorij i pristup, te Resend nalog s domenom i SPF/DKIM zapisima na
  Hurricane Electric DNS-u — DNS prvi, zbog propagacije.

Tih pet sati bez ijedne linije koda je razlog zašto kasnije nije bilo rušenja i
prepravljanja: svaka faza je znala u šta se uklapa prije nego je počela, i zato je
ostatak posla mogao ići dva dana ispred plana.

| Dan | Plan iz `ROADMAP.md` | Stvarno isporučeno | Commita | Prvi–zadnji commit |
|---|---|---|---|---|
| **Sri 22.07 (22–01)** | — (rad počinje po prijemu zadatka) | Razrada plana, `ROADMAP`/`CLAUDE`/`DATA_MODEL`, priprema okruženja i DNS/Resend | 0 | prije prvog commita |
| **Čet 23.07** | Faza 0 + 0.5, pa start Faze 1 | Dovršetak dokumentacije (09–11, pa prvi push), **Faza 0** (Laravel+Filament skeleton, Docker, CI), **Faza 0.5** (produkcijski stack, Apache reverse proxy, auto-deploy, Resend) | 14 | 11:03 – 23:13 |
| **Pet 24.07** | Dovršiti Fazu 1, Faza 2 | **Faza 1** (platform jezgro), **Faza 2** (dashboard, tema, brzo dodavanje), **Faza 3** (Zadaci + Kanban + Kalendar) + QA, univerzalna pretraga, **Faza 4** (Podsjetnici + Bilješke), rekonstrukcija brzog dodavanja | 25 | 06:54 – 23:12 |
| **Sub 25.07** | Faza 3 | **Faza 5a** (Finansije), **Faza 5b** (Life admin), **Faza 6a** (dijeljenje + članovi), **Faza 6b** (obavještenja po članu + digest), in-app sanduče, tri QA kruga kroz cijeli sistem | 13 | 07:11 – 21:33 |
| **Ned 26.07** | Faza 4 + Faza 5 | Četvrti QA krug, **Faza 7a/7b/7c** (app registry, probna app, pozivnice), **Faza 8** (backup, health, *testiran rollback na produkciji*), **Faza 9a/9b/9c** (rebrend, tri jezika, završni prolaz) | 17 | 04:14 – 15:30 |
| **Pon 27.07** | Faza 6, 7, minimalna 8, kratka 9, finalizacija | — *nije bio potreban* | 0 | — |

**Odstupanja od plana i zašto:**

- **Dva dana ispred plana kroz cijeli projekat.** Faza 3 je isporučena u petak
  (planirano: subota), Faza 5 u subotu (planirano: nedjelja), a Faze 6–9 u
  nedjelju (planirano: ponedjeljak). Zato je vrijeme predviđeno za „ako nešto
  pukne" iskorišteno za kvalitet, ne za nadoknađivanje.
- **Faza 9 je jedina faza koja je narasla, a ne skratila se.** Plan je izričito
  govorio da se ona prva skraćuje ako dođe do kašnjenja; pošto kašnjenja nije
  bilo, podijeljena je na 9a (rebrend), 9b (tri jezika) i 9c (završni prolaz), uz
  zahtjeve vlasnika koji su u nju ušli usput.
- **Velike faze su same tražile podjelu.** 5a/5b, 6a/6b, 7a/7b/7c i 9a/9b/9c nisu
  bile u planu kao koraci — podijeljene su da bi vlasnik mogao provjeriti i
  potvrditi svaki dio prije nastavka, što je dio grešaka uhvatilo ranije.
- **Četiri QA kruga umjesto jednog finalnog prolaza.** Umjesto da se ispravke
  gomilaju za kraj, prošli su kao zasebni krugovi (petak, subota ×3, nedjelja) —
  terminologija, mobilni prikaz, 500 na kreiranju domaćinstva, konsolidacija
  postavki. `RULES.md` je nastao upravo iz tih krugova.
- **Najveći pojedinačni gubitak vremena:** univerzalna pretraga. Livewire
  komponenta u Filament render hooku je iza proxyja obarala `/livewire/update` na
  419 (snapshot/checksum), pa je rješenje prošlo kroz pet pokušaja dok nije
  završilo kao čisti Alpine + `fetch` JSON, bez Livewire round-tripa. Isti
  obrazac je onda primijenjen i na brzo dodavanje, i oba od tada rade bez 419.

---

# Brojevi na kraju

| Mjera | Vrijednost |
|---|---|
| Testovi (Pest) | **224**, svi prolaze |
| Tvrdnji (assertions) | **858** |
| Trajanje punog seta | ~10 min (sqlite u memoriji) |
| Test fajlova | 52 |
| Commita | 69 (14 / 25 / 13 / 17 po danima) |
| PHP fajlova u `app/` | 231 (~12.200 linija) |
| Modula (`app/Modules/*`) | 7 (Tasks, Reminders, Notes, Finance, LifeAdmin, Pets, Calendar) |
| Migracija | 30 |
| Eventa / listenera | 17 / 15 |
| Policy klasa | 12 |
| Filament Resources / Pages | 12 / 8 |
| Notifikacija | 9 |
| Platformskih kontrakata | 4 (Dashboard, Search, Calendar, Digest) + QuickCreate |
| Prijevodnih vrijednosti | ~1.960 u tri jezika (bs 613, en 670, de 680 — razlika je Laravelov puni set validacijskih pravila u en/de) |
| Jezika | 3 (bs, en, de), s testom parnosti ključeva |

---

# Zaključak: šta je ušlo, a zadatak to nije tražio

Zadatak (`ORIGINAL_SPEC.md`) opisuje **šta aplikacija treba raditi** — module,
dijeljenje, email obavještenja i proširivost. Ne spominje hosting, isporuku,
sigurnost, testove, jezike ni dokumentaciju. Sve niže je zato dodano kao dio
onoga što ovakav sistem čini upotrebljivim u stvarnom domaćinstvu, a ne samo
demonstracijom:

**Isporuka i pouzdanost**

- **Auto-deployment na svaki push u `main`** (GitHub Actions → SSH → Docker
  Compose), s migracijama i backupom baze prije njih. `main` je uvijek u stanju
  spremnom za produkciju, jer CI mora biti zelen prije mergea.
- **Rollback je testiran na živoj produkciji, ne pretpostavljen.** Verzija je
  podignuta na 1.0.1, deployana, pa `git revert`-om vraćena na 1.0.0 — uz
  provjeru da su podaci ostali netaknuti (12 zadataka, 22 podsjetnika, 3
  ljubimca, 6 računa, 8 članova prije i poslije). Taj test je i otkrio da naziv i
  verzija ne smiju živjeti u `.env` na serveru, pa su premješteni u kod.
- **Noćni backup** baze i priloga u 03:15, s automatskim brisanjem starijih od 14
  dana i **email upozorenjem ako backup ne uspije** — jer backup o kojem niko ne
  zna kad je pukao nije backup.
- **`/health` endpoint** (baza, cache, storage, verzija), koji koristi i deploy da
  potvrdi da je nova verzija živa. Namjerno **bez** rate limitera: brojači
  limitera žive u cacheu, pa bi na pokvarenom cacheu endpoint vratio 500 upravo
  kad mu je posao da prijavi `cache: false`.
- **CI kao kapija:** Pint + puni Pest set na svaki push i pull request.

**Sigurnost i izolacija**

- Docker stack sluša **isključivo na loopbacku** (`127.0.0.1:8091`), a SSL
  terminira Apache/Virtualmin — kontejner nema ni sertifikat ni javni port, i ne
  dira desetine drugih domena na istom serveru.
- **Izolovan MySQL korisnik i baza**, s pravima ograničenim samo na svoju bazu.
- **Prilozi i profilne slike su na privatnom disku** i idu kroz autentikovanu
  rutu — dokument domaćinstva nikad ne postaje javni URL.
- **Throttle na javnim rutama** i na endpointu koji upisuje podatke, uz testove
  koji provjeravaju i granice i izolaciju domaćinstava (tuđi `?h=` i tuđi tenant
  → 404, ne 403, da se ne potvrdi ni postojanje).

**Upotrebljivost koju zadatak nije tražio**

- **Tri jezika** (bosanski, engleski, njemački) s prekidačem sa zastavicama,
  **emailovima na jeziku primaoca** i testom parnosti ključeva koji nedostajući
  prijevod čini greškom, a ne sirovim ključem na ekranu.
- **Uključivanje/isključivanje aplikacija po domaćinstvu** — isključena app
  nestaje iz menija, dashboarda, pretrage, kalendara i brzog dodavanja, a podaci
  ostaju i vraćaju se kad se ponovo uključi.
- **Pozivnica putem linka** za osobe koje još nemaju nalog (token s rokom od 7
  dana, email zaključan na formi registracije).
- **Valuta kao postavka domaćinstva** (29 valuta), primijenjena na svim formama i
  pregledima.
- **Tamna tema, pristupačnost** (vidljiv keyboard focus, `prefers-reduced-motion`,
  `prefers-reduced-transparency`, kontrast po WCAG AA) i **mobile-first izgled
  provjeren na tri širine**, uz vlastiti vizuelni identitet („Topli dom") umjesto
  neizmijenjenog Filament admin izgleda.
- **Vlastiti stil emailova** s logotipom aplikacije i istim potpisom i verzijom
  kao u aplikaciji, umjesto Laravelovog default izgleda.

**Način rada koji je ostao zapisan**

- **Četiri živa dokumenta** (`CLAUDE.md`, `RULES.md`, `DATA_MODEL.md`,
  `ROADMAP.md`) — ne opis onoga što je napravljeno, nego pravila po kojima se radi
  dalje: checklista za novi modul, terminologija i pravopis korisničkog teksta,
  konvencije imenovanja polja, pravilo da su rute engleske.
- **Pravilo naučeno kroz greške:** ako test zamijeni produkcijski sloj (lažni
  dumper, `Notification::fake()`, `array` cache), mora postojati i test koji
  vježba **pravi** — jer `Notification::fake()` nikad ne pozove `toMail()`, pa su
  emailovi jedno vrijeme tiho padali dok su testovi bili zeleni.

**Šta zadatak spominje, a nije izgrađeno:** korisnički vidljiv graditelj
automatizacija („kada se ovo desi, uradi ono"). Temelj za njega postoji i
koristi se — 17 domenskih eventa i 15 listenera kroz koje moduli sarađuju bez
međusobnog poznavanja — ali ekran na kojem član domaćinstva sam sastavlja pravilo
nije bio u obimu 11 faza i nije improvizovan u zadnji čas.
