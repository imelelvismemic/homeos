<!--
Ovaj dokument je namijenjen ljudima koji ocjenjuju rješenje, ne Claude Code
agentu. Za operativni rad na projektu vrijede ROADMAP.md, CLAUDE.md,
DATA_MODEL.md i docs/ORIGINAL_SPEC.md.

Dijelovi označeni [POPUNITI ...] se dopunjuju kako projekat napreduje kroz
faze iz ROADMAP.md — ovaj dokument se ažurira usput, ne piše se tek na
kraju.
-->

# Home OS — Sažetak rješenja

**Autor:** Elvis Memić
**Alat:** Claude Code (kompletna izrada, od arhitekture do deploymenta)

---

## Šta je zadatak tražio

Lični "kućni operativni sistem" — jedna aplikacija koja objedinjuje
svakodnevnu administraciju domaćinstva (zadaci, kalendar, podsjetnici,
finansije, bilješke...), dijeljena između članova domaćinstva, izgrađena
kao **platforma** na koju se mogu dodavati nove aplikacije bez izmjene
postojećeg koda. Zadatak je dobijen kao PDF na engleskom, bez definisane
tehnologije i bez smjernica za deployment.

Puni izvorni tekst: `docs/ORIGINAL_SPEC.md` (prijevod).

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
- **`docs/ORIGINAL_SPEC.md`** — izvorni brief, referenca za namjeru kad
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
- **`docs/PRAVILA.md`** — pravila terminologije i pravopisa za sav
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

[POPUNITI nakon Faze 7 — ROADMAP.md]

Plan dokaza: dodati probnu "dummy" aplikaciju prateći checklist iz
`CLAUDE.md` (tačka 14) i pokazati da se pojavi na dashboardu, u pretrazi i
navigaciji bez izmjene postojećeg koda. Ovdje će biti opisan konkretan
primjer i (ako je moguće) kratak snimak/screenshot postupka.

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
  s `docs/PRAVILA.md` (malo slovo u drugoj riječi); **brisanje budžeta** prikazuje
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
  zapisano u `docs/PRAVILA.md` (§1, §3, §7, §8) da se ne ponovi u novim modulima.

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
  formu dodavanja s koje je korisnik došao — pravilo u `docs/PRAVILA.md` §9 i u
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
